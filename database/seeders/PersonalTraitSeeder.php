<?php

namespace Database\Seeders;

use App\Models\PersonalTrait;
use Illuminate\Database\Seeder;

class PersonalTraitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PersonalTrait::updateOrCreate(
            ['category' => 'about_me'],
            [
                'content' => '你好，我是柯智勛（Chieh-Hsun），一名擁有高度自律性與解決問題熱忱的後端工程師。專注於使用 PHP/Laravel 與現代化架構設計解決高併發、高安全性的系統痛點。我熱愛探究底層原理與防超賣等業務架構設計，立志在後端領域深耕，為團隊帶來實質產出與架構優化。',
            ]
        );

        PersonalTrait::updateOrCreate(
            ['category' => 'career_path'],
            [
                'content' => '我是一名非本科轉職者，在過去的一年中，我透過自律且系統性的學習，快速掌握了電腦科學基礎、資料庫設計（如索引優化、ACID 特性）與 Laravel 現代化後端架構。相較於本科生，我具備更多元跨領域的思考與極強的適應力，能以務實且靈活的態度面對新技術的挑戰，並於實際業務中快速落地。',
            ]
        );

        PersonalTrait::updateOrCreate(
            ['category' => 'soft_skills'],
            [
                'content' => '【自律學習與抗壓特質】：我具備極強的自我驅動力，習慣在工作之餘規劃技術深造（如探究 Redis 分布式鎖、資料庫交易機制等）。在面對高難度、高併發的核心業務時，我能展現極強的抗壓性，冷靜拆解並分析根本原因。【主動溝通】：我擅長將複雜的後端技術架構以簡明的方式向 HR 或跨部門同仁進行溝通，並主動對接前端 Workers 協作，具有優異的團隊協作與跨領域對話特質。',
            ]
        );
    }
}
