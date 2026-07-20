<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Project::updateOrCreate(
            ['name' => 'laravel-ticket'],
            [
                'title' => '高併發 Redis 防超賣票券系統',
                'tech_stack' => 'Laravel 12, PHP 8.2+, Redis, PostgreSQL, Docker, Nginx',
                'challenge' => '當熱門演唱會門票開賣時，瞬間湧入萬級請求，直接存取資料庫會導致連線崩潰與 Race Condition 造成的票券超賣。',
                'solution' => '採用 Redis 進行庫存預減與請求排隊，限制資料庫的寫入壓力。透過 Lua 腳本結合 Redis 交易以保證庫存檢查與扣減的原子性（Atomicity），並於背景使用 Queue（Laravel Queue + Redis Queue）非同步地將預約訂單寫入 PostgreSQL 資料庫，徹底防止超賣。',
                'achievement' => '成功承載每秒萬級（10,000+ RPS）高併發請求，且資料庫寫入維持在平滑安全水位，超賣率降為 0%。',
            ]
        );

        Project::updateOrCreate(
            ['name' => 'payroll-automation'],
            [
                'title' => '失業給付與薪資處理自動化系統',
                'tech_stack' => 'Laravel 11, PHP 8.2+, SQLite, Excel Import',
                'challenge' => '人力資源處每月需要耗費大量時間人工審核、比對與計算失業給付及薪資報表，過程繁雜且容易出錯。',
                'solution' => '開發自動化導入與解析系統，實現自動化比對，並設計多段補償機制（Compensation Transaction）以處理例外流程，確保資料的一致性與交易安全。',
                'achievement' => '自動化處理率達到 100%，將原先需要 3 天的人工作業縮短至 5 分鐘內完成，處理正確率提升至 100%。',
            ]
        );

        Project::updateOrCreate(
            ['name' => 'interview-system'],
            [
                'title' => '智能雙向面試助理系統',
                'tech_stack' => 'Laravel 12、PHP 8.5、PostgreSQL (Render)、Cloudflare Workers (Serverless)、Gemini API (Function Calling)、GitHub Pages',
                'challenge' => "在 0 預算（僅投資網域）的前提下，要打造一個能動態讀寫資料庫的 AI 面試系統，面臨了三個棘手的架構挑戰：\n\n免費雲端平台（Render Free Tier）的冷啟動冬眠機制：Render 免費版容器在 15 分鐘無人使用後會自動進入休眠狀態。當 HR 首次點擊網頁提問時，伺服器開機需要 30 到 60 秒的空白等待期，極易導致 HR 誤判網站損壞而直接關閉網頁。\n\n前端靜態網頁（GitHub Pages）的敏感金鑰暴露危機：GitHub Pages 屬於純前端靜態代管，所有 JavaScript 程式碼在瀏覽器中完全公開。若直接在前端呼叫 Gemini API，其金鑰（API Key）將面臨 F12 開發者工具一秒被竊取、盜刷的巨大安全漏洞。\n\nLLM 大模型的資訊幻覺（Hallucination）與行為不可控：直接對接大模型時，AI 容易脫離現實瞎掰履歷細節，或無法控制其回應語氣。且在不租用固定後端伺服器的情況下，極難讓 AI 具備「主動去資料庫翻閱正確答案」以及「幫 HR 異步寫入預約資料」的 Agent 代理能力。",
                'solution' => "Serverless 邊緣網關與密鑰隔離：拒絕在前端（GitHub Pages）暴露 Gemini API 金鑰。改採用 Cloudflare Workers 建立無伺服器中轉層，將 AI 密鑰安全隔離於雲端後端，徹底杜絕前端密鑰洩漏危機。\n\nAI Agent (Function Calling) 雙向閉環：向 Gemini 1.5 Flash 注入 JSON Schema 工具箱。當 HR 語意涉及「想了解專案踩坑經驗」或「預約面試」時，由 Worker 攔截 AI 的工具調用指令（Function Call），非同步調用底層 Laravel 12 API 進行資料庫（PostgreSQL）的 CRUD 讀寫，再將結果餵回 AI 進行語意組織。\n\n端到端安全簽章防護：為了防止 Render 後端 API 網址暴露而遭受惡意爆破，在 Laravel 12 實作自訂特徵簽章認證中介層（Signature Middleware），僅允許帶有 Worker 加密金鑰（X-Worker-Signature）的請求通過，建立嚴格的防禦網。",
                'achievement' => "基礎設施 0 元託管與 100% 穩定度：完美整合 GitHub Pages、Cloudflare 邊緣運算、Render 容器與 Render PostgreSQL 免費額度，以極限 0 元成本成功讓商業級 AI Agent 架構落地。\n\n全面解決 Render 冷啟動（Cold Start）卡頓痛點：利用 Cloudflare Worker 秒開、不休眠的特性，在 50 毫秒內優先吐出 UI 打字機特效與「AI 秘書翻閱中...」的緩衝動畫，並於背景非同步喚醒 Render 容器，成功消除了免費版因休眠導致的 30 秒網頁空白等待期，大幅優化 HR 的使用留存率。\n\n24 小時無人值守自動化求職流水線：串接 Laravel 12 Mailable 系統。當 HR 透過前端 AI 聊天室成功輸入面試約定時，後端於 1.5 秒內自動觸發異步通知信至本人信箱，實現高科技、高效率的獵才主動對接。",
            ]
        );
    }
}
