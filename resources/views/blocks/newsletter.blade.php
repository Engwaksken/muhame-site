<section class="mx-auto max-w-7xl px-5 py-10 sm:px-8 sm:py-14">
    <div class="grid gap-8 rounded-3xl bg-ink px-8 py-12 text-white sm:px-12 lg:grid-cols-2 lg:items-center">
        <div>
            <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ filled($data['heading'] ?? null) ? $data['heading'] : $siteSettings->newsletterHeading() }}</h2>
            <p class="mt-4 text-lg leading-8 opacity-85">{{ filled($data['text'] ?? null) ? $data['text'] : $siteSettings->newsletterText() }}</p>
        </div>
        <div>@include('partials.newsletter-form', ['formId' => 'block-'.$blockIndex, 'dark' => true])</div>
    </div>
</section>
