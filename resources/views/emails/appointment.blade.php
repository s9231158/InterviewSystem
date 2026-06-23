<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>HR 面試預約通知</title>
    <style>
        body {
            font-family: "Microsoft JhengHei", sans-serif;
            background-color: #f4f6f9;
            color: #333333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            background-color: #ffffff;
            margin: 0 auto;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-top: 6px solid #4f46e5;
            overflow: hidden;
        }
        .header {
            background-color: #f8fafc;
            padding: 24px;
            border-bottom: 1px solid #e2e8f0;
            text-align: center;
        }
        .header h2 {
            margin: 0;
            color: #4f46e5;
            font-size: 20px;
        }
        .content {
            padding: 32px 24px;
            line-height: 1.6;
        }
        .content p {
            margin: 0 0 16px 0;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 24px 0;
        }
        .details-table td {
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
        }
        .details-table td.label {
            font-weight: bold;
            color: #475569;
            width: 120px;
        }
        .details-table td.value {
            color: #0f172a;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>面試助理通知：收到新的面試預約</h2>
        </div>
        <div class="content">
            <p>您好，柯智勛（Chieh-Hsun）：</p>
            <p>您的 AI 線上面試助理系統剛剛收到了一筆來自 HR 的面試預約，詳細資訊如下：</p>
            
            <table class="details-table">
                <tr>
                    <td class="label">HR 姓名</td>
                    <td class="value">{{ $interview->hr_name }}</td>
                </tr>
                <tr>
                    <td class="label">公司名稱</td>
                    <td class="value">{{ $interview->company_name }}</td>
                </tr>
                <tr>
                    <td class="label">預約時間</td>
                    <td class="value">{{ $interview->interview_time->format('Y-m-d H:i') }}</td>
                </tr>
                <tr>
                    <td class="label">聯絡資訊</td>
                    <td class="value">{{ $interview->contact_info }}</td>
                </tr>
            </table>
            
            <p>請您主動與該 HR 取得聯繫以確認細節。祝您面試順利！</p>
        </div>
        <div class="footer">
            <p>此信件由您的 AI 面試助理系統（InterviewSystem）自動發送，請勿直接回覆。</p>
        </div>
    </div>
</body>
</html>
