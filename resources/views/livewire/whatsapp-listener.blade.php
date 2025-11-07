<script>
document.addEventListener('lpw-open-whatsapp', event => {
    const url = event?.detail?.url;

    if (!url) {
        return;
    }

    window.open(url, '_blank', 'noopener,noreferrer');
});
</script>

