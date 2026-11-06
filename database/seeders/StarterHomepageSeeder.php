<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/** Imports the public eHub landing page as editable, unpublished CMS pages. */
class StarterHomepageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            ['title' => 'Home', 'slug' => 'home', 'is_homepage' => true, 'show_in_navigation' => false,
                'excerpt' => 'Applied research and program design connecting out-of-school youth, women, and refugees in Uganda to skills, certification, and dignified work.',
                'blocks' => [
                    ['type' => 'hero', 'data' => ['heading' => 'We study how out-of-school youth actually get to work. Then we build the pathway that gets them there.', 'text' => 'Applied research and program design for young people, women, and refugees excluded from Uganda’s formal education and labor systems — delivered through the eHub platform.', 'button_label' => 'Partner with us', 'button_url' => 'mailto:hello@bboobbogroup.com']],
                    ['type' => 'stats', 'data' => ['heading' => 'Research · Learn · Certify · Place', 'layout' => 'band', 'items' => [['value' => '55', 'label' => 'youth placed to date (Kuwait & Saudi Arabia, 2024–2025)'], ['value' => '8 weeks', 'label' => 'to job-ready vs ~18 months traditional'], ['value' => '2022', 'label' => 'Founded in Kampala']]]],
                    ['type' => 'text', 'data' => ['heading' => 'A complete pathway to dignified work', 'layout' => 'centered', 'content' => '<p>eHub connects assessment, adaptive learning, certification, and job placement in one platform — designed around the realities of out-of-school youth.</p><p>We work with funders, employers, and public programs to build a documented, evidence-based route from out-of-school to dignified work.</p>']],
                    ['type' => 'cta_banner', 'data' => ['heading' => 'Partner with eHub', 'text' => 'Talk to us about a pilot, placement partnership, or research collaboration.', 'button_label' => 'Get in touch', 'button_url' => 'mailto:hello@bboobbogroup.com']],
                ]],
            ['title' => 'The Problem', 'slug' => 'the-problem', 'show_in_navigation' => true, 'navigation_order' => 1,
                'excerpt' => 'The scale of exclusion is structural, not marginal.', 'blocks' => [
                    ['type' => 'stats', 'data' => ['heading' => 'The scale of exclusion', 'layout' => 'cards', 'items' => [['value' => '39%', 'label' => 'of Uganda’s youth aged 18–30 are not in employment, education, or training (NEET); 50.5% among young women vs 29% among young men. Source: NPA (2021); Bbaale et al. (2023, 2024).'], ['value' => '43%', 'label' => 'of employed Ugandan youth are own-account workers; over nine in ten young workers are in informal or non-formal employment. Source: UBOS, ALFS 2018/19.'], ['value' => '9+ programs', 'label' => 'Uganda has introduced at least nine youth employment programs since 2008, yet spending remains below 0.15% of GDP. Source: Mabugu & Ronconi (2024).']]]],
                    ['type' => 'text', 'data' => ['heading' => 'Programs exist. A complete pathway does not.', 'content' => '<p>Independent reviews find youth employment programs individually reasonably effective, but collectively disjointed, without a system to coordinate national and local action.</p><p>Rural youth are often unaware programs exist. One-day financial-literacy training has proven inadequate for managing revolving loan funds. Major programs are not specifically designed for out-of-school young women, refugees, and mothers who cannot meet standard eligibility requirements.</p><p>Existing pathways can take about two years and cost the equivalent of $13,350 (UGX 50M) in lost income, with no guaranteed route to a job. eHub exists to close this pathway gap.</p>']],
                ]],
            ['title' => 'Our Method', 'slug' => 'our-method', 'show_in_navigation' => true, 'navigation_order' => 2,
                'excerpt' => 'Evidence-based learning and placement through eHub NeLP®.', 'blocks' => [
                    ['type' => 'text', 'data' => ['heading' => 'Why structured skills training and placement matter', 'content' => '<p>Research with disadvantaged, out-of-school Ugandan youth found vocational training gains persisted longer than apprenticeship gains, in part because a completion certificate lets workers prove skills to a new employer. (Alfonsi et al., 2020, Econometrica.)</p><p>eHub builds training around how adults with fragmented schooling learn: short, context-anchored units; spaced retrieval; and scaffolded discovery.</p>']],
                    ['type' => 'cards', 'data' => ['heading' => 'Four learning experiences', 'intro' => 'NeLP® (Neuroscientific Learning Pattern) starts from the behaviour that must change, grounded in measurable cognitive constructs.', 'columns' => '2', 'layout' => 'cards', 'cards' => [['title' => 'Generation of knowledge', 'text' => 'Start from prior knowledge and real operational context; new understanding is generated, not received. Evidence: Slamecka & Graf (1978); Bruner (1961).'], ['title' => 'Contextualized exemplification', 'text' => 'Situate content in a real operational scenario, not an abstract rule. Evidence: Morris, Bransford & Franks (1977); Sweller (1988).'], ['title' => 'Active reworking', 'text' => 'Learners decide, act, and make errors inside branching scenarios. Evidence: Bjork (1994); Roediger & Karpicke (2006).'], ['title' => 'Spaced training and certification', 'text' => 'Short sessions support retention and end in a verified, resume-ready credential. Evidence: Cepeda et al. (2006); Alfonsi et al. (2020).']]]],
                    ['type' => 'text', 'data' => ['heading' => 'The learner journey', 'content' => '<ol><li>Sign up</li><li>Get assessed</li><li>Learn adaptively</li><li>Get certified</li><li>Get matched</li><li>Get placed</li></ol><p>One learner, one platform, from first lesson to first paycheck. Eight weeks via eHub versus approximately 18 months on the traditional path.</p>']],
                ]],
            ['title' => 'Field Work', 'slug' => 'field-work', 'show_in_navigation' => true, 'navigation_order' => 3,
                'excerpt' => 'Training goes to where people live and work.', 'blocks' => [
                    ['type' => 'text', 'data' => ['heading' => 'Training goes to where people live and work', 'content' => '<p>eHub’s first cohorts met where they already are: a hub office in Kampala, a courtyard between wooden houses, and a community space in an informal settlement. The platform is tested on real phones with real learners in the conditions they face.</p><p>No campus, no commute. eHub runs on a basic smartphone, online or with intermittent connectivity. Assessment comes first, then an adaptive learning path. Short, situated, spoken content makes prior schooling less of a barrier.</p>']],
                ]],
            ['title' => 'Our Team', 'slug' => 'our-team', 'show_in_navigation' => true, 'navigation_order' => 4,
                'excerpt' => 'A team built around the whole pathway.', 'blocks' => [
                    ['type' => 'cards', 'data' => ['heading' => 'A team built around the whole pathway', 'intro' => 'A history of supporting youth empowerment initiatives at community, sub-national, and national levels.', 'columns' => '3', 'layout' => 'minimal', 'cards' => [['title' => 'Rossette Komuhangi', 'text' => 'Founder & Executive Director. Recruitment and workforce-placement experience inside a MoGLSD-registered agency. Computer Science, St. Lawrence University.'], ['title' => 'Helen Kobusingye', 'text' => 'Senior Knowledge and Training Manager. Master Trainer leading soft-skills and communication instruction. Certified Education and Training Degree, Kabale University.'], ['title' => 'Alon Muhame', 'text' => 'EdTech Researcher & Advisor. Field experiments and education researcher advising on the AI and data systems behind eHub’s adaptive learning engine.']]]],
                    ['type' => 'cta_banner', 'data' => ['heading' => 'Work with eHub', 'text' => 'Partner with us on a pilot, placement partnership, or research collaboration.', 'button_label' => 'Partner with us', 'button_url' => 'mailto:hello@bboobbogroup.com']],
                ]],
            ['title' => 'References', 'slug' => 'references', 'show_in_navigation' => true, 'navigation_order' => 5,
                'excerpt' => 'Research and evidence behind the eHub approach.', 'blocks' => [
                    ['type' => 'text', 'data' => ['heading' => 'The evidence behind the approach', 'content' => '<ol><li>Alfonsi et al. (2020). Tackling Youth Unemployment: Evidence From a Labor Market Experiment in Uganda. <em>Econometrica</em>, 88(6), 2369–2414.</li><li>Bandiera et al. (2012, 2020). Empowering Adolescent Girls / Women’s Empowerment in Action: Evidence from Uganda. World Bank / BRAC.</li><li>Bbaale et al. (2023). What Works for Youth Employment in Africa: A Review of Youth Employment Policies and Their Impact in Uganda. PEP Working Paper 2023-16.</li><li>Bbaale et al. (2024). An Empirical Review of Youth Employment Policies and Programmes in Uganda. PEP Policy Brief No. 276.</li><li>Blattman, Fiala & Martinez (2014). Generating Skilled Self-Employment in Developing Countries: Experimental Evidence from Uganda. <em>Quarterly Journal of Economics</em>, 129(2), 697–752.</li><li>Mabugu & Ronconi (2024). The Implementation of Youth Employment Programs in Seven Nations in Sub-Saharan Africa. PEP Working Paper 2024-07.</li><li>Uganda Bureau of Statistics (2019). Annual Labour Force Survey 2018/19.</li><li>Sweller (1988). Cognitive load during problem solving. <em>Cognitive Science</em>, 12(2), 257–285.</li><li>Ebbinghaus (1885). <em>Über das Gedächtnis</em>.</li><li>Bruner (1961). The act of discovery. <em>Harvard Educational Review</em>, 31, 21–32.</li><li>Morris, Bransford & Franks (1977). Levels of processing versus transfer appropriate processing. <em>Journal of Verbal Learning and Verbal Behavior</em>, 16(5), 519–533.</li><li>Bjork (1994). Memory and metamemory considerations in the training of human beings. MIT Press.</li><li>Roediger & Karpicke (2006). Test-enhanced learning. <em>Psychological Science</em>, 17(3), 249–255.</li><li>Slamecka & Graf (1978). The generation effect. <em>Journal of Experimental Psychology</em>, 4(6), 592–604.</li><li>Cepeda et al. (2006). Distributed practice in verbal recall tasks. <em>Psychological Bulletin</em>, 132(3), 354–380.</li></ol>']],
                ]],
        ];

        foreach ($pages as $attributes) {
            if (Page::query()->where('slug', $attributes['slug'])->exists() || (($attributes['is_homepage'] ?? false) && Page::query()->where('is_homepage', true)->exists())) {
                continue;
            }

            Page::query()->create([...$attributes, 'status' => 'draft']);
        }
    }
}
