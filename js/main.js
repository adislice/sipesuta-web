
document.addEventListener("DOMContentLoaded", function(){
    // Tambahkan shadow ke navbar saat scrolling
    window.addEventListener('scroll', function() {
        if (window.scrollY > 8) {
            document.getElementById('brandText').classList.add('text-primary');
            document.getElementById('brandText').classList.remove('text-white');
            document.getElementById('landing_nav').classList.remove('py-5');
            document.getElementById('landing_nav').classList.add('shadow', 'bg-base-100/95');
        } else {
            document.getElementById('brandText').classList.add('text-white');
            document.getElementById('brandText').classList.remove('text-primary');
            document.getElementById('landing_nav').classList.remove('shadow', 'bg-base-100/95');
            document.getElementById('landing_nav').classList.add('py-5');
        } 
    });
}); 
