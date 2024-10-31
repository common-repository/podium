<!--BEGIN PODIUM SCRIPT-->
<script type="text/javascript">
    (function (n,r,l,d) {
        try {
            var h=r.head||r.getElementsByTagName("head")[0],s=r.createElement("script");
            s.id = "podium-widget"
            s.defer = true;
            s.async = true;
            s.setAttribute('data-organization-api-token', d)
            s.setAttribute("src",l);
            h.appendChild(s);
        } catch (e) {}
    })(window,document,"https://connect.podium.com/widget.js", <?php echo wp_is_uuid(strval($podium_script_code)) == true ? "'" . strval($podium_script_code) . "'" : ''; ?>);
</script>
<!--END PODIUM SCRIPT-->