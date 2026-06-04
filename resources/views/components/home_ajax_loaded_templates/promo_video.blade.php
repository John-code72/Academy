
<link rel="stylesheet" href="{{ asset('assets/global/plyr/plyr.css') }}">


    <video id="promoPlayer" playsinline controls>
        <source src="{{asset('assets/global/video/banner.mp4'')}}" type="video/mp4">
    </video>

<script src="{{ asset('assets/global/plyr/plyr.js') }}"></script>
<script>
    "use strict";
    var promoPlayer = new Plyr('#promoPlayer');
</script>
<script>
    "use strict";
    const myModalElPromo = document.getElementById('promoVideo')
    myModalElPromo.addEventListener('hidden.bs.modal', event => {
        promoPlayer.pause();
        $('#promoVideo').toggleClass('in');
    });
    myModalElPromo.addEventListener('shown.bs.modal', event => {
        promoPlayer.play();
        $('#promoVideo').toggleClass('in');
    });

    
</script>