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
    }
}
