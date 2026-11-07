<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

/**
 * Imports the public eHub site as editable CMS pages.
 *
 * The public menu is grouped into four tabs:
 *   1. About us    — Who we are, Problem & Gap, Meet our team
 *   2. Solution    — eHub as a solution, Why it works, Field
 *   3. Methodology — the method itself, with References as a child page
 *   4. Blogs       — a link to the published posts at /insights
 */
class StarterHomepageSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->pages() as $attributes) {
            $parentSlug = $attributes['parent_slug'] ?? null;
            unset($attributes['parent_slug']);

            if ($parentSlug !== null) {
                $attributes['parent_id'] = Page::query()->where('slug', $parentSlug)->value('id');
            }

            $page = Page::query()->where('slug', $attributes['slug'])->first();

            if ($page) {
                $this->syncStructure($page, $attributes);
                $this->addMissingSections($page, $attributes['slug']);

                continue;
            }

            Page::query()->create([...$attributes, 'status' => ($attributes['is_homepage'] ?? false) ? 'draft' : 'published']);
        }

        $this->addBlogsNavigationLink();
        $this->addMenuBuilders();
    }

    /**
     * Keep the menu hierarchy in sync without overwriting an editor's own
     * title, excerpt or page content after the first import.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function syncStructure(Page $page, array $attributes): void
    {
        $legacyTitles = [
            'the-problem' => 'The Problem',
            'our-method' => 'Our Method',
            'field-work' => 'Field Work',
            'our-team' => 'Our Team',
            'references' => 'References',
        ];

        $updates = [
            'parent_id' => $attributes['parent_id'] ?? null,
            'show_in_navigation' => $attributes['show_in_navigation'] ?? false,
            'navigation_order' => $attributes['navigation_order'] ?? 0,
        ];

        // Only rename the untouched pages created by the original import.
        if (($legacyTitles[$attributes['slug']] ?? null) === $page->title) {
            $updates['title'] = $attributes['title'];
        }

        if (blank($page->excerpt) && filled($attributes['excerpt'] ?? null)) {
            $updates['excerpt'] = $attributes['excerpt'];
        }

        // Starter pages used to be drafts. Publish untouched starter pages so
        // the grouped menu and its children appear on the public site.
        if ($page->status === 'draft' && in_array($page->title, $legacyTitles, true)) {
            $updates['status'] = 'published';
        }

        $page->forceFill($updates)->saveQuietly();
    }

    /** Add newly introduced sections to an already-imported page, once. */
    private function addMissingSections(Page $page, string $slug): void
    {
        $blocks = collect(is_array($page->blocks) ? $page->blocks : []);

        // Upgrade the original, imported method page to the graphic showcase.
        if ($slug === 'our-method' && ! $blocks->contains(
            fn ($block) => is_array($block) && ($block['type'] ?? null) === 'methodology_showcase',
        )) {
            $starterHeadings = [
                'Why structured skills training and placement matter',
                'Four learning experiences',
                'The learner journey',
            ];

            $blocks = $blocks
                ->reject(fn ($block) => is_array($block) && in_array($block['data']['heading'] ?? null, $starterHeadings, true))
                ->prepend(['type' => 'methodology_showcase', 'data' => self::methodologyShowcase()])
                ->values();

            $page->forceFill(['blocks' => $blocks->all()])->saveQuietly();

            return;
        }

        $hasHeading = fn (string $heading): bool => $blocks->contains(
            fn ($block) => is_array($block) && ($block['data']['heading'] ?? null) === $heading,
        );

        $candidates = match ($slug) {
            'the-problem' => [
                ['heading' => 'Four gaps that shape the design', 'block' => ['type' => 'cards', 'data' => $this->problemGapCards()]],
            ],
            'field-work' => [
                ['heading' => 'What the field taught us', 'block' => ['type' => 'cards', 'data' => $this->fieldCards()]],
                ['heading' => 'First tests, first learners', 'block' => ['type' => 'stats', 'data' => $this->fieldStats()]],
                ['heading' => 'Where we work', 'block' => ['type' => 'text', 'data' => $this->fieldLocations()]],
            ],
            'our-team' => [
                ['heading' => 'Board of directors', 'block' => ['type' => 'cards', 'data' => $this->boardCards()]],
            ],
            default => [],
        };

        $missing = collect($candidates)
            ->reject(fn (array $candidate) => $hasHeading($candidate['heading']))
            ->pluck('block')
            ->values();

        if ($missing->isEmpty()) {
            return;
        }

        // Keep the call-to-action banner as the last section where possible.
        $ctaIndex = $blocks->search(fn ($block) => is_array($block) && ($block['type'] ?? null) === 'cta_banner');
        $blocks->splice($ctaIndex === false ? $blocks->count() : $ctaIndex, 0, $missing->all());

        $page->forceFill(['blocks' => $blocks->values()->all()])->saveQuietly();
    }

    private function addBlogsNavigationLink(): void
    {
        $settings = SiteSetting::current();
        $links = is_array($settings->navigation_links) ? $settings->navigation_links : [];

        if (array_key_exists('Blogs', $links)) {
            return;
        }

        $settings->forceFill(['navigation_links' => [...$links, 'Blogs' => '/insights']])->saveQuietly();
    }

    /**
     * Pre-fill the admin menu builders once, using the grouped pages above.
     * Editors can then reorder, relabel, or replace every item in Site settings.
     */
    private function addMenuBuilders(): void
    {
        $settings = SiteSetting::current();

        if ($settings->main_menu === null) {
            $menu = [];

            foreach (['about-us', 'solution', 'our-method'] as $slug) {
                $page = Page::query()->where('slug', $slug)->first();

                if (! $page) {
                    continue;
                }

                $children = Page::query()
                    ->where('parent_id', $page->getKey())
                    ->where('status', 'published')
                    ->orderBy('navigation_order')
                    ->orderBy('title')
                    ->get()
                    ->map(fn (Page $child) => [
                        'type' => 'page',
                        'page_id' => $child->getKey(),
                        'label' => $child->title,
                    ])
                    ->all();

                $menu[] = [
                    'type' => 'page',
                    'page_id' => $page->getKey(),
                    'label' => $page->title,
                    'children' => $children,
                ];
            }

            $menu[] = ['type' => 'link', 'label' => 'Blogs', 'url' => '/insights'];
            $settings->main_menu = $menu;
        }

        if ($settings->footer_columns === null) {
            $footerPages = collect(['about-us', 'solution', 'our-method', 'our-team'])
                ->map(function (string $slug): ?array {
                    $page = Page::query()->where('slug', $slug)->where('status', 'published')->first();

                    return $page ? ['type' => 'page', 'page_id' => $page->getKey(), 'label' => $page->title] : null;
                })
                ->filter()
                ->values()
                ->all();

            $settings->footer_columns = [
                ['title' => 'Explore', 'items' => [
                    ['type' => 'link', 'label' => 'Blogs', 'url' => '/insights'],
                    ['type' => 'link', 'label' => 'Events', 'url' => '/events'],
                    ['type' => 'link', 'label' => 'Contact', 'url' => '/contact'],
                ]],
                ['title' => 'Pages & links', 'items' => $footerPages],
            ];
        }

        $settings->saveQuietly();
    }

    /** @return array<int, array<string, mixed>> */
    private function pages(): array
    {
        return [
            // ── Home ────────────────────────────────────────────────────────
            ['title' => 'Home', 'slug' => 'home', 'is_homepage' => true, 'show_in_navigation' => false,
                'excerpt' => 'Applied research and program design connecting out-of-school youth, women, and refugees in Uganda to skills, certification, and dignified work.',
                'blocks' => [
                    ['type' => 'hero', 'data' => ['heading' => 'We study how out-of-school youth actually get to work. Then we build the pathway that gets them there.', 'text' => 'Applied research and program design for young people, women, and refugees excluded from Uganda’s formal education and labor systems — delivered through the eHub platform.', 'button_label' => 'Partner with us', 'button_url' => 'mailto:hello@bboobbogroup.com']],
                    ['type' => 'stats', 'data' => ['heading' => 'Research · Learn · Certify · Place', 'layout' => 'band', 'items' => [['value' => '55', 'label' => 'youth placed to date (Kuwait & Saudi Arabia, 2024–2025)'], ['value' => '8 weeks', 'label' => 'to job-ready vs ~18 months traditional'], ['value' => '2022', 'label' => 'Founded in Kampala']]]],
                    ['type' => 'text', 'data' => ['heading' => 'A complete pathway to dignified work', 'layout' => 'centered', 'content' => '<p>eHub connects assessment, adaptive learning, certification, and job placement in one platform — designed around the realities of out-of-school youth.</p><p>We work with funders, employers, and public programs to build a documented, evidence-based route from out-of-school to dignified work.</p>']],
                    ['type' => 'cta_banner', 'data' => ['heading' => 'Partner with eHub', 'text' => 'Talk to us about a pilot, placement partnership, or research collaboration.', 'button_label' => 'Get in touch', 'button_url' => 'mailto:hello@bboobbogroup.com']],
                ]],

            // ── 1. About us ─────────────────────────────────────────────────
            ['title' => 'About us', 'slug' => 'about-us', 'show_in_navigation' => true, 'navigation_order' => 1,
                'excerpt' => 'Who we are, the gap we exist to close, and the people behind eHub.',
                'blocks' => [
                    ['type' => 'text', 'data' => ['heading' => 'Applied research and program design, built around the learner', 'layout' => 'centered', 'content' => '<p>eHub is the learning and placement platform of BBOOBBO Green Group, founded in Kampala in 2022. We study how out-of-school youth, women, and refugees actually get to work — then build the pathway that gets them there.</p><p>Our work brings field research, instructional design, certification, and employer partnerships into one documented route from first lesson to first paycheck.</p>']],
                    ['type' => 'cards', 'data' => ['heading' => 'Explore About us', 'intro' => 'Three ways to understand the organisation and the problem we work on.', 'columns' => '3', 'layout' => 'minimal', 'cards' => [
                        ['title' => 'Who we are', 'text' => 'Our mission, model, and the experience behind the platform.', 'url' => '/who-we-are', 'link_label' => 'Read more'],
                        ['title' => 'Problem & Gap', 'text' => 'Why existing programmes leave so many young people behind.', 'url' => '/the-problem', 'link_label' => 'Read more'],
                        ['title' => 'Meet our team', 'text' => 'The leadership team and board guiding eHub.', 'url' => '/our-team', 'link_label' => 'Read more'],
                    ]]],
                ]],

            ['title' => 'Who we are', 'slug' => 'who-we-are', 'parent_slug' => 'about-us', 'show_in_navigation' => true, 'navigation_order' => 1,
                'excerpt' => 'An applied research and program-design organisation working where education and labour systems leave people out.',
                'blocks' => [
                    ['type' => 'text', 'data' => ['heading' => 'Who we are', 'content' => '<p>eHub is an applied research and programme-design organisation based in Kampala, Uganda. We work with young people, women, and refugees who have been excluded from formal education and labour systems — and we design the pathway that reconnects them to skills, certification, and dignified work.</p><p>Our team combines recruitment and workforce-placement experience, master-trainer expertise in soft skills and communication, and field-experiment research in education technology. That mix is deliberate: the platform is built by people who have run programmes on the ground, not only in a classroom.</p>']],
                    ['type' => 'cards', 'data' => ['heading' => 'What we do', 'columns' => '3', 'layout' => 'cards', 'cards' => [
                        ['title' => 'Applied research', 'text' => 'We study what actually happens to out-of-school learners in the labour market, and which programme designs change their outcomes.'],
                        ['title' => 'Program design', 'text' => 'We turn evidence into short, situated learning pathways with assessment, practice, and a verified credential at the end.'],
                        ['title' => 'Placement partnerships', 'text' => 'We connect certified learners to employers and public programmes that need reliable, job-ready talent.'],
                    ]]],
                    ['type' => 'stats', 'data' => ['heading' => 'eHub at a glance', 'layout' => 'band', 'items' => [
                        ['value' => '55', 'label' => 'youth placed to date (Kuwait & Saudi Arabia, 2024–2025)'],
                        ['value' => '8 weeks', 'label' => 'to job-ready vs ~18 months on the traditional path'],
                        ['value' => '2022', 'label' => 'founded in Kampala, Uganda'],
                    ]]],
                ]],

            ['title' => 'Problem & Gap', 'slug' => 'the-problem', 'parent_slug' => 'about-us', 'show_in_navigation' => true, 'navigation_order' => 2,
                'excerpt' => 'The scale of exclusion is structural, not marginal.',
                'blocks' => [
                    ['type' => 'stats', 'data' => ['heading' => 'The scale of exclusion', 'layout' => 'cards', 'items' => [
                        ['value' => '39%', 'label' => 'of Uganda’s youth aged 18–30 are not in employment, education, or training (NEET); 50.5% among young women vs 29% among young men. Source: NPA (2021); Bbaale et al. (2023, 2024).'],
                        ['value' => '43%', 'label' => 'of employed Ugandan youth are own-account workers; over nine in ten young workers are in informal or non-formal employment. Source: UBOS, ALFS 2018/19.'],
                        ['value' => '9+ programs', 'label' => 'Uganda has introduced at least nine youth employment programs since 2008, yet spending remains below 0.15% of GDP. Source: Mabugu & Ronconi (2024).'],
                    ]]],
                    ['type' => 'text', 'data' => ['heading' => 'Programs exist. A complete pathway does not.', 'content' => '<p>Independent reviews find youth employment programs individually reasonably effective, but collectively disjointed, without a system to coordinate national and local action.</p><p>Rural youth are often unaware programs exist. One-day financial-literacy training has proven inadequate for managing revolving loan funds. Major programs are not specifically designed for out-of-school young women, refugees, and mothers who cannot meet standard eligibility requirements.</p><p>Existing pathways can take about two years and cost the equivalent of $13,350 (UGX 50M) in lost income, with no guaranteed route to a job. eHub exists to close this pathway gap.</p>']],
                    ['type' => 'cards', 'data' => $this->problemGapCards()],
                ]],

            ['title' => 'Meet our team', 'slug' => 'our-team', 'parent_slug' => 'about-us', 'show_in_navigation' => true, 'navigation_order' => 3,
                'excerpt' => 'A team built around the whole pathway.',
                'blocks' => [
                    ['type' => 'cards', 'data' => ['heading' => 'Leadership team', 'intro' => 'A history of supporting youth empowerment initiatives at community, sub-national, and national levels.', 'columns' => '3', 'layout' => 'minimal', 'image_ratio' => 'portrait', 'cards' => [
                        ['title' => 'Rossette Komuhangi', 'text' => 'Founder & Executive Director. Recruitment and workforce-placement experience inside a MoGLSD-registered agency. Computer Science, St. Lawrence University.'],
                        ['title' => 'Helen Kobusingye', 'text' => 'Senior Knowledge and Training Manager. Master Trainer leading soft-skills and communication instruction. Certified Education and Training Degree, Kabale University.'],
                        ['title' => 'Alon Muhame', 'text' => 'EdTech Researcher & Advisor. Field experiments and education researcher advising on the AI and data systems behind eHub’s adaptive learning engine.'],
                    ]]],
                    ['type' => 'cards', 'data' => $this->boardCards()],
                    ['type' => 'cta_banner', 'data' => ['heading' => 'Work with eHub', 'text' => 'Partner with us on a pilot, placement partnership, or research collaboration.', 'button_label' => 'Partner with us', 'button_url' => 'mailto:hello@bboobbogroup.com']],
                ]],

            // ── 2. Solution ─────────────────────────────────────────────────
            ['title' => 'Solution', 'slug' => 'solution', 'show_in_navigation' => true, 'navigation_order' => 2,
                'excerpt' => 'One platform that connects assessment, adaptive learning, certification, and placement.',
                'blocks' => [
                    ['type' => 'text', 'data' => ['heading' => 'One pathway, from first lesson to first paycheck', 'layout' => 'centered', 'content' => '<p>eHub is a complete pathway rather than another stand-alone training course. Assessment comes first, then an adaptive learning path, a verified credential, and a structured route into employment.</p><p>It is designed for people who learn on their feet and on their own phones — in the courtyard, the workshop, and the market stall.</p>']],
                    ['type' => 'cards', 'data' => ['heading' => 'Explore the solution', 'columns' => '3', 'layout' => 'minimal', 'cards' => [
                        ['title' => 'eHub as a solution', 'text' => 'How assessment, learning, certification, and placement fit together.', 'url' => '/ehub-as-a-solution', 'link_label' => 'Read more'],
                        ['title' => 'Why it works', 'text' => 'The evidence on certification, cognitive load, spacing, and scaffolded discovery.', 'url' => '/why-it-works', 'link_label' => 'Read more'],
                        ['title' => 'Field', 'text' => 'What the first cohorts and field sites have taught us.', 'url' => '/field-work', 'link_label' => 'Read more'],
                    ]]],
                ]],

            ['title' => 'eHub as a solution', 'slug' => 'ehub-as-a-solution', 'parent_slug' => 'solution', 'show_in_navigation' => true, 'navigation_order' => 1,
                'excerpt' => 'Assessment, adaptive learning, certification, and placement in one platform.',
                'blocks' => [
                    ['type' => 'text', 'data' => ['heading' => 'A complete pathway to dignified work', 'content' => '<p>eHub connects the parts that are usually delivered separately. A learner starts with an assessment of what they already know, follows a path that adapts to their pace, earns a credential an employer can verify, and is matched to a placement.</p><p>Because the platform owns the whole journey, progress is visible at every stage — to the learner, the trainer, the funder, and the employer.</p>']],
                    ['type' => 'learner_journey', 'data' => ['heading' => 'THE LEARNER JOURNEY', 'steps' => [['label' => 'Sign up'], ['label' => 'Get assessed'], ['label' => 'Learn adaptively'], ['label' => 'Get certified'], ['label' => 'Get matched'], ['label' => 'Get placed']], 'note' => 'One learner, one platform, from first lesson to first paycheck. 8 weeks via eHub vs approximately 18 months on the traditional path.']],
                    ['type' => 'cards', 'data' => ['heading' => 'What makes it different', 'columns' => '3', 'layout' => 'cards', 'cards' => [
                        ['title' => 'Assessment first', 'text' => 'No learner starts from zero. The platform establishes prior knowledge and builds the shortest credible route to competence.'],
                        ['title' => 'Built for real phones', 'text' => 'Short, situated, spoken content runs online or with intermittent connectivity, so training reaches the courtyard and the market stall.'],
                        ['title' => 'A credential that travels', 'text' => 'Learners leave with a documented, resume-ready skill signal that an employer can verify — not just a certificate of attendance.'],
                    ]]],
                ]],

            ['title' => 'Why it works', 'slug' => 'why-it-works', 'parent_slug' => 'solution', 'show_in_navigation' => true, 'navigation_order' => 2,
                'excerpt' => 'Certification makes training gains last; instruction is built around how adults actually learn.',
                'blocks' => [
                    ['type' => 'text', 'data' => ['heading' => 'Two design lessons, one pathway', 'content' => '<p>Local fieldwork and rigorous experimental evidence point to the same conclusion from two directions: training must end in a credential that employers trust, and it must be built the way the adult brain actually learns.</p><p>eHub is designed around both lessons.</p>']],
                    ['type' => 'cards', 'data' => ['heading' => 'Three principles from cognitive science', 'columns' => '3', 'layout' => 'numbered', 'cards' => [
                        ['title' => 'Cognitive load', 'text' => 'Working memory is narrow. Learners with limited prior schooling are overloaded fastest by abstract, front-loaded content. Instruction is sequenced in small, context-anchored units. Source: Sweller (1988).'],
                        ['title' => 'Forgetting and spacing', 'text' => 'Without reinforcement, most of what is learned is forgotten within days. Retention depends on spaced, repeated retrieval — not a single long session. Source: Ebbinghaus (1885).'],
                        ['title' => 'Scaffolded discovery', 'text' => 'Skills are built when learners actively construct understanding with graduated support, starting from what they already know and do. Source: Bruner (1960s).'],
                    ]]],
                    ['type' => 'text', 'data' => ['heading' => 'Certification makes training gains last', 'content' => '<p>A randomized experiment with disadvantaged, out-of-school Ugandan youth compared classroom vocational training against firm-based apprenticeships. Both produced similar skill gains, but apprenticeship gains faded within three years while vocational-training gains persisted — because a completion certificate let workers prove their skills to a new employer after a setback.</p><p>That is why eHub issues a documented, resume-ready skill signal rather than training alone. Source: Alfonsi et al. (2020), <em>Econometrica</em>, 88(6), 2369–2414.</p>']],
                ]],

            ['title' => 'Field', 'slug' => 'field-work', 'parent_slug' => 'solution', 'show_in_navigation' => true, 'navigation_order' => 3,
                'excerpt' => 'Training goes to where people live and work.',
                'blocks' => [
                    ['type' => 'text', 'data' => ['heading' => 'Training goes to where people live and work', 'content' => '<p>eHub’s first cohorts met where they already are: a hub office in Kampala, a courtyard between wooden houses, and a community space in an informal settlement. The platform is tested on real phones with real learners in the conditions they face.</p><p>No campus, no commute. eHub runs on a basic smartphone, online or with intermittent connectivity. Assessment comes first, then an adaptive learning path. Short, situated, spoken content makes prior schooling less of a barrier.</p>']],
                    ['type' => 'cards', 'data' => $this->fieldCards()],
                    ['type' => 'stats', 'data' => $this->fieldStats()],
                    ['type' => 'text', 'data' => $this->fieldLocations()],
                ]],

            // ── 3. Methodology ──────────────────────────────────────────────
            ['title' => 'Methodology', 'slug' => 'our-method', 'show_in_navigation' => true, 'navigation_order' => 3,
                'excerpt' => 'Evidence-based learning and placement through eHub NeLP®.',
                'blocks' => [
                    ['type' => 'methodology_showcase', 'data' => self::methodologyShowcase()],
                ]],

            ['title' => 'References', 'slug' => 'references', 'parent_slug' => 'our-method', 'show_in_navigation' => true, 'navigation_order' => 1,
                'excerpt' => 'Research and evidence behind the eHub approach.',
                'blocks' => [
                    ['type' => 'text', 'data' => ['heading' => 'The evidence behind the approach', 'content' => $this->referencesHtml()]],
                ]],
        ];
    }

    /** @return array<string, mixed> */
    private function problemGapCards(): array
    {
        return [
            'heading' => 'Four gaps that shape the design',
            'intro' => 'The reviews identify specific, documented weaknesses — and each one is designed into eHub.',
            'columns' => '2',
            'layout' => 'numbered',
            'cards' => [
                ['title' => 'Awareness and targeting gaps', 'text' => 'Fieldwork across nine Ugandan districts found many rural youth simply unaware these programmes exist, with some programmes implicitly favouring urban, better-off, or already-schooled applicants.'],
                ['title' => 'Preparation gaps', 'text' => 'One-day financial-literacy training has proven inadequate for managing revolving loan funds, contributing to fund misuse independent of participants’ intentions.'],
                ['title' => 'A population gap', 'text' => 'None of Uganda’s major existing programmes is built specifically around out-of-school young women, refugees, and mothers who dropped out years ago and cannot meet standard eligibility requirements.'],
                ['title' => 'A pathway gap', 'text' => 'Existing training pathways for NEET youth take about two years and cost the equivalent of $13,350 (UGX 50M) in lost income. They offer no structured path and no guaranteed route to a job.'],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function fieldCards(): array
    {
        return [
            'heading' => 'What the field taught us',
            'intro' => 'Three design choices came directly from running the platform with real cohorts.',
            'columns' => '3',
            'layout' => 'cards',
            'cards' => [
                ['title' => 'Where the person is', 'text' => 'No campus and no commute. eHub runs on a basic smartphone, online or with intermittent connectivity, so training reaches the courtyard, the workshop, and the market stall.'],
                ['title' => 'Adapted to each individual', 'text' => 'Assessment first, then a path that speeds up, slows down, and re-orders itself around what this learner already knows and how quickly they consolidate a skill.'],
                ['title' => 'Every level of schooling', 'text' => 'Learners who left school years ago and learners who finished secondary sit in the same cohort. Content is short, situated, and spoken as much as written, so prior schooling stops being the entry ticket.'],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function fieldStats(): array
    {
        return [
            'heading' => 'First tests, first learners',
            'layout' => 'inline',
            'items' => [
                ['value' => '3', 'label' => 'field sites: a Kampala hub office, an informal-settlement courtyard, and a community outreach space'],
                ['value' => '1st', 'label' => 'cohorts onboarded and assessed on their own phones, not in a computer lab'],
                ['value' => '8 weeks', 'label' => 'to job-ready, tested in the conditions learners actually face'],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function fieldLocations(): array
    {
        return [
            'heading' => 'Where we work',
            'content' => '<p>Our first delivery sites are in Kampala and its informal settlements, with community outreach run alongside the Rokhrate Innovation Hub team. Each site is chosen because learners are already there — not because a classroom is available.</p><p>As cohorts grow, the same model travels: a partner space, a local trainer, and a phone in the learner’s hand.</p>',
        ];
    }

    /** @return array<string, mixed> */
    private function boardCards(): array
    {
        return [
            'heading' => 'Board of directors',
            'intro' => 'Our board provides strategic oversight, governance, and accountability for the eHub mission.',
            'columns' => '2',
            'layout' => 'minimal',
            'image_ratio' => 'portrait',
            'cards' => [
                ['title' => 'Mr. Andrea', 'text' => 'Board Member. Strategic oversight and governance of eHub’s mission, partnerships, and public accountability.'],
                ['title' => 'Dr. Mugenyi', 'text' => 'Board Member. Strategic oversight and governance of eHub’s research agenda, quality, and institutional development.'],
            ],
        ];
    }

    private function referencesHtml(): string
    {
        return '<ol>'
            .'<li>Alfonsi, L., Bandiera, O., Bassi, V., Burgess, R., Rasul, I., Sulaiman, M., &amp; Vitali, A. (2020). Tackling Youth Unemployment: Evidence From a Labor Market Experiment in Uganda. <em>Econometrica</em>, 88(6), 2369–2414.</li>'
            .'<li>Bandiera, O., Buehren, N., Burgess, R., Goldstein, M., Gulesci, S., Rasul, I., &amp; Sulaiman, M. (2012, 2020). Empowering Adolescent Girls / Women’s Empowerment in Action: Evidence from a Randomized Control Trial in Uganda. World Bank / BRAC.</li>'
            .'<li>Bbaale, E., Babyenda, P., Kavuma, S., Kiconco, B., Kyamugabwa, A., Sebukeera, H., Nakigudde, C., Wainaina, S., &amp; Mabugu, R. (2023). What Works for Youth Employment in Africa: A Review of Youth Employment Policies and Their Impact in Uganda. PEP Working Paper 2023-16.</li>'
            .'<li>Bbaale, E., Babyenda, P., Kavuma, S., Kiconco, B., Kyamugabwa, A., Sebukeera, H., &amp; Nakigudde, C. (2024). An Empirical Review of Youth Employment Policies and Programmes in Uganda. PEP Policy Brief No. 276.</li>'
            .'<li>Blattman, C., Fiala, N., &amp; Martinez, S. (2014). Generating Skilled Self-Employment in Developing Countries: Experimental Evidence from Uganda. <em>Quarterly Journal of Economics</em>, 129(2), 697–752.</li>'
            .'<li>Mabugu, R., &amp; Ronconi, L. (2024). The Implementation of Youth Employment Programs in Seven Nations in Sub-Saharan Africa: A Synthesis of Country Studies. PEP Working Paper 2024-07.</li>'
            .'<li>Uganda Bureau of Statistics (UBOS). (2019). Annual Labour Force Survey 2018/19.</li>'
            .'<li>Sweller, J. (1988). Cognitive load during problem solving: Effects on learning. <em>Cognitive Science</em>, 12(2), 257–285.</li>'
            .'<li>Ebbinghaus, H. (1885). <em>Über das Gedächtnis</em>. Leipzig: Duncker &amp; Humblot.</li>'
            .'<li>Bruner, J. S. (1961). The act of discovery. <em>Harvard Educational Review</em>, 31, 21–32.</li>'
            .'<li>Morris, C. D., Bransford, J. D., &amp; Franks, J. J. (1977). Levels of processing versus transfer appropriate processing. <em>Journal of Verbal Learning and Verbal Behavior</em>, 16(5), 519–533.</li>'
            .'<li>Bjork, R. A. (1994). Memory and metamemory considerations in the training of human beings. In J. Metcalfe &amp; A. Shimamura (Eds.), <em>Metacognition: Knowing about knowing</em> (pp. 185–205). MIT Press.</li>'
            .'<li>Roediger, H. L., &amp; Karpicke, J. D. (2006). Test-enhanced learning: Taking memory tests improves long-term retention. <em>Psychological Science</em>, 17(3), 249–255.</li>'
            .'<li>Slamecka, N. J., &amp; Graf, P. (1978). The generation effect: Delineation of a phenomenon. <em>Journal of Experimental Psychology: Human Learning and Memory</em>, 4(6), 592–604.</li>'
            .'<li>Cepeda, N. J., Pashler, H., Vul, E., Wixted, J. T., &amp; Rohrer, D. (2006). Distributed practice in verbal recall tasks: A review and quantitative synthesis. <em>Psychological Bulletin</em>, 132(3), 354–380.</li>'
            .'</ol>';
    }

    /** Editable eHub methodology content arranged to mirror the source page's graphic composition. */
    private static function methodologyShowcase(): array
    {
        return [
            'eyebrow' => '04 · THE METHODOLOGY',
            'heading' => 'The methodology: eHub NeLP®',
            'intro' => 'Four learning experiences, each grounded in evidence.',
            'about_heading' => 'ABOUT NeLP®',
            'about_content' => 'NeLP® (Neuroscientific Learning Pattern) is a proprietary instructional-design method. It starts from the behaviour that must change and is founded on measurable cognitive constructs — cognitive load, working memory, spacing effect, testing effect, and dual coding — rather than “learning styles”. Its reference platform, NeLPx®, is built for frontline workers who learn on the job, not at a desk.',
            'highlights' => [
                ['title' => '−30% Time to Competence · +55% Retention Rate', 'text' => 'Laboratory studies; real-world validation in progress.', 'source' => 'Source: NeLP® laboratory studies'],
                ['title' => 'First in Uganda — and first in Africa', 'text' => 'eHub is the first platform in Uganda and on the African continent to implement NeLP® end to end.', 'source' => 'A method designed around how learners actually learn and work.'],
                ['title' => 'Best Methodology Award 2026', 'text' => 'Recognised at the Innovation Training Summit 2026.', 'source' => 'EFI'],
                ['title' => 'Recognised internationally', 'text' => 'Selected for the UCL EdTech accelerator and named among the top 10 most innovative EdTech startups.', 'source' => 'UCL / Santander X (2024); EdTechX Awards (2024)'],
            ],
            'experiences' => [
                ['number' => '01', 'title' => 'Generation of knowledge', 'text' => 'The learner starts from prior knowledge and real operational context; new understanding is generated, not received.', 'evidence' => 'Generation Effect (Slamecka & Graf, 1978); Bruner (1961).'],
                ['number' => '02', 'title' => 'Contextualized exemplification', 'text' => 'Content is situated in a real operational scenario — a bakery, a care home, a job site — not an abstract rule.', 'evidence' => 'Transfer-Appropriate Processing (Morris, Bransford & Franks, 1977); Sweller (1988).'],
                ['number' => '03', 'title' => 'Active reworking', 'text' => 'The learner decides, acts, and makes errors inside branching scenarios; error is part of the design.', 'evidence' => 'Desirable Difficulties (Bjork, 1994); Testing Effect (Roediger & Karpicke, 2006).'],
                ['number' => '04', 'title' => 'Quick, spaced, generalizable training and certification', 'text' => 'Short sessions are sequenced on neuroscientific, not editorial, grounds, supporting retention without re-teaching and ending in a verified credential.', 'evidence' => 'Spacing Effect (Ebbinghaus, 1885; Cepeda et al., 2006); Alfonsi et al. (2020).'],
            ],
            'journey' => [
                ['label' => 'Sign up'], ['label' => 'Get assessed'], ['label' => 'Learn adaptively'],
                ['label' => 'Get certified'], ['label' => 'Get matched'], ['label' => 'Get placed'],
            ],
            'journey_note' => 'One learner, one platform, from first lesson to first paycheck. 8 weeks via eHub vs approximately 18 months on the traditional path.',
        ];
    }
}
