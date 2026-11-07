<section class="mx-auto max-w-7xl px-5 py-8 sm:px-8 sm:py-10">
    <div class="grid gap-8 rounded-3xl bg-ink px-6 py-8 text-white sm:px-10 sm:py-10 lg:grid-cols-2 lg:items-center">
        <div>
            <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ filled($data['heading'] ?? null) ? $data['heading'] : $siteSettings->newsletterHeading() }}</h2>
            <p class="mt-3 text-lg leading-8 opacity-85">{{ filled($data['text'] ?? null) ? $data['text'] : $siteSettings->newsletterText() }}</p>
        </div>
        <div>@include('partials.newsletter-form', ['formId' => 'block-'.$blockIndex, 'dark' => true])</div>
    </div>
</section>
