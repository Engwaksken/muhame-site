<section class="mx-auto max-w-7xl px-5 py-5 sm:px-8 sm:py-6">
    <div class="grid gap-6 rounded-3xl bg-ink px-6 py-5 text-white sm:px-10 sm:py-6 lg:grid-cols-2 lg:items-center">
        <div>
            <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ filled($data['heading'] ?? null) ? $data['heading'] : $siteSettings->newsletterHeading() }}</h2>
            <p class="mt-2 text-lg leading-8 opacity-85">{{ filled($data['text'] ?? null) ? $data['text'] : $siteSettings->newsletterText() }}</p>
        </div>
        <div>@include('partials.newsletter-form', ['formId' => 'block-'.$blockIndex, 'dark' => true])</div>
    </div>
</section>
