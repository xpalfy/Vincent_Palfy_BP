document.addEventListener('DOMContentLoaded', function () {
    function showTime() {
        let time = new Date();
        let hour = time.getHours();
        let min = time.getMinutes();
        let sec = time.getSeconds();
        hour =
            hour < 10 ? "0" + hour : hour;
        min = min < 10 ? "0" + min : min;
        sec = sec < 10 ? "0" + sec : sec;
        document.getElementById(
            "clock"
        ).innerHTML = hour +
            ":" +
            min +
            ":" +
            sec;
    }

    setInterval(showTime, 1000);
    showTime();
});
