/**
 * Cloudflare Worker: Chieh-Hsun's AI Interview Agent
 * Integrates Gemini 1.5 Flash with Function Calling over Laravel backend API.
 */

const CORS_HEADERS = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Methods': 'GET, POST, OPTIONS',
  'Access-Control-Allow-Headers': 'Content-Type, X-Worker-Signature',
  'Access-Control-Max-Age': '86400',
};

// System instruction specifying the agent persona and constraints
const SYSTEM_INSTRUCTION = {
  parts: [{
    text: "你現在是柯智勛（Chieh-Hsun）的 AI 面試代理人。智勛是一位專業、踏實且具有高度解決問題能力的後端工程師，擁有豐富的 Laravel 實戰經驗。請用親切、專業、自信的口吻回答 HR。當被問到專案細節、特質經歷或面試約定時，你必須主動決定呼叫對應的工具，不能憑空捏造。若使用者提問無關智勛的面試與職涯，請禮貌地婉拒並引導回智勛的專業能力上。一律使用台灣繁體中文回答。"
  }]
};

// Tool declarations for Gemini Function Calling
const TOOLS = [{
  functionDeclarations: [
    {
      name: "get_project_detail",
      description: "當問及搶票系統、專案踩坑經驗、技術架構、高併發瓶頸或解決方案時使用。",
      parameters: {
        type: "OBJECT",
        properties: {
          projectName: {
            type: "STRING",
            description: "專案的唯一名稱標識，例如 laravel-ticket 或 payroll-automation"
          }
        },
        required: ["projectName"]
      }
    },
    {
      name: "get_personal_profile",
      description: "當問及智勛的轉職經歷、個人特質、為什麼想當工程師、學習動機或軟實力時使用。",
      parameters: {
        type: "OBJECT",
        properties: {}
      }
    },
    {
      name: "schedule_interview",
      description: "當 HR 想要約時間面試、留下聯絡方式以確定約定面談時間時主動觸發。這會在資料庫建立一筆預約並發送郵件通知智勛。",
      parameters: {
        type: "OBJECT",
        properties: {
          hrName: {
            type: "STRING",
            description: "聯絡人的姓名"
          },
          companyName: {
            type: "STRING",
            description: "公司或組織名稱"
          },
          dateTime: {
            type: "STRING",
            description: "面試預約的日期與時間，格式必須為 YYYY-MM-DD HH:mm:ss"
          },
          contactInfo: {
            type: "STRING",
            description: "聯絡資訊，例如電話號碼或 Email 信箱"
          }
        },
        required: ["hrName", "companyName", "dateTime", "contactInfo"]
      }
    }
  ]
}];

export default {
  async fetch(request, env, ctx) {
    // 1. Handle CORS preflight options
    if (request.method === 'OPTIONS') {
      return new Response(null, { headers: CORS_HEADERS });
    }

    if (request.method !== 'POST') {
      return new Response(JSON.stringify({ error: 'Method not allowed' }), {
        status: 405,
        headers: { 'Content-Type': 'application/json', ...CORS_HEADERS }
      });
    }

    try {
      // 2. Validate environment configurations (Fail Close)
      if (!env.GEMINI_KEY || !env.LARAVEL_API_URL || !env.WORKER_SECRET) {
        console.error('[Configuration Error] Missing vital environment variables.');
        return new Response(JSON.stringify({ error: 'Internal Server Security Configuration Error.' }), {
          status: 500,
          headers: { 'Content-Type': 'application/json', ...CORS_HEADERS }
        });
      }

      const { message, history = [] } = await request.json();

      if (!message) {
        return new Response(JSON.stringify({ error: 'Message is required' }), {
          status: 400,
          headers: { 'Content-Type': 'application/json', ...CORS_HEADERS }
        });
      }

      // 3. Assemble chat content structure for Gemini API
      // We map historical messages to fit Gemini request format: { role: 'user'|'model', parts: [{ text: ... }] }
      const contents = [...history];
      contents.push({
        role: 'user',
        parts: [{ text: message }]
      });

      let finalResponseText = '';
      let loopCount = 0;
      const maxLoops = 3; // Safety check to prevent infinite function calling loops

      while (loopCount < maxLoops) {
        const geminiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5:generateContent?key=${env.GEMINI_KEY}`;

        const response = await fetch(geminiUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            contents,
            systemInstruction: SYSTEM_INSTRUCTION,
            tools: TOOLS,
          })
        });

        if (!response.ok) {
          const errText = await response.text();
          throw new Error(`Gemini API error: ${response.status} - ${errText}`);
        }

        const data = await response.json();
        const candidate = data.candidates?.[0];
        const content = candidate?.content;
        const parts = content?.parts || [];

        // Append Gemini's response to the conversation history to maintain context
        contents.push({
          role: content?.role || 'model',
          parts: parts
        });

        // 4. Check for tool calls (Function Calls)
        const functionCallPart = parts.find(p => p.functionCall);

        if (functionCallPart) {
          const { name, args } = functionCallPart.functionCall;
          console.log(`[Agent Decision] Executing Tool Call: ${name}`, args);

          let toolResult = {};

          try {
            // Execute the appropriate backend API call securely (incorporating Worker Secret header)
            if (name === 'get_project_detail') {
              const url = `${env.LARAVEL_API_URL}/api/projects/${encodeURIComponent(args.projectName)}`;
              const res = await fetch(url, {
                headers: { 'X-Worker-Signature': env.WORKER_SECRET }
              });
              toolResult = res.ok ? await res.json() : { error: `Failed to fetch project details: ${res.status}` };
            } else if (name === 'get_personal_profile') {
              const url = `${env.LARAVEL_API_URL}/api/profile`;
              const res = await fetch(url, {
                headers: { 'X-Worker-Signature': env.WORKER_SECRET }
              });
              toolResult = res.ok ? await res.json() : { error: `Failed to fetch profile: ${res.status}` };
            } else if (name === 'schedule_interview') {
              const url = `${env.LARAVEL_API_URL}/api/appointments`;
              const res = await fetch(url, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-Worker-Signature': env.WORKER_SECRET
                },
                body: JSON.stringify({
                  hr_name: args.hrName,
                  company_name: args.companyName,
                  interview_time: args.dateTime,
                  contact_info: args.contactInfo
                })
              });
              toolResult = res.ok ? await res.json() : { error: `Failed to schedule interview: ${res.status}` };
            } else {
              toolResult = { error: `Unknown tool function: ${name}` };
            }
          } catch (apiError) {
            console.error(`[API Error] Failed in calling Laravel backend.`, apiError);
            toolResult = { error: 'Backend server connection failed. Please try again later.' };
          }

          // Insert the function response back to the contents array to feed to Gemini
          contents.push({
            role: 'user',
            parts: [{
              functionResponse: {
                name,
                response: toolResult
              }
            }]
          });

          loopCount++;
        } else {
          // No more function calls, we have the final natural language answer
          finalResponseText = parts.find(p => p.text)?.text || '';
          break;
        }
      }

      if (loopCount >= maxLoops) {
        console.warn('[Loop Limit] Maximum function calling loop limit reached.');
        finalResponseText = finalResponseText || '抱歉，系統在處理工具調用時超時，請重新提問。';
      }

      // 5. Send back the response text and updated history
      return new Response(JSON.stringify({
        reply: finalResponseText,
        history: contents
      }), {
        headers: { 'Content-Type': 'application/json', ...CORS_HEADERS }
      });

    } catch (err) {
      console.error('[Error] Worker Execution Failure.', err);
      // Secure response: Generic safe error output (Fail Safe)
      return new Response(JSON.stringify({
        error: '面試助理暫時遇到系統異常。請稍後再試。'
      }), {
        status: 500,
        headers: { 'Content-Type': 'application/json', ...CORS_HEADERS }
      });
    }
  }
};
