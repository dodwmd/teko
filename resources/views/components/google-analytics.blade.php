@if(config('services.google.analytics.enabled') && config('services.google.analytics.measurement_id'))
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics.measurement_id') }}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ config('services.google.analytics.measurement_id') }}');
    
    // Custom event tracking helper
    window.trackEvent = function(category, action, label = null, value = null) {
        if (typeof gtag === 'function') {
            gtag('event', action, {
                'event_category': category,
                'event_label': label,
                'value': value
            });
        }
    };
</script>
@endif
