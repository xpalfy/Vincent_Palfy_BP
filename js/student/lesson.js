function startTestSlovak(lessonId, database, testName, canStartTest, waitTime) {
    if (canStartTest) {
        window.location.href = "/html/student/slovensky/test.php?id=" + lessonId + "&database=" + database + "&test=" + testName;
    } else {
        toastr.error("Please wait " + waitTime + " more minute(s) before retaking the test. Take this time to learn more for the next try!");
    }
}

function startTestEnglish(lessonId, database, testName, canStartTest, waitTime) {
    if (canStartTest) {
        window.location.href = "/html/student/english/test.php?id=" + lessonId + "&database=" + database + "&test=" + testName;
    } else {
        toastr.error("Please wait " + waitTime + " more minute(s) before retaking the test. Take this time to learn more for the next try!");
    }
}

function updateButtonStatus(lessonId, passed, waitTime) {
    let button = document.getElementById('testButton' + lessonId);
    if (passed === 1) {
        button.disabled = true;
        let text = document.createElement('p');
        text.style.marginTop = '10px';
        text.className = 'text-muted';
        //if the url consists of slovensky
        if (window.location.href.includes('slovensky')) {
            text.innerHTML = 'Tento test ste už absolvovali';
        } else {
            text.innerHTML = 'You have already passed this test';
        }
        button.parentNode.appendChild(text);
    } else if (passed === 0 && waitTime > 0) {
        button.disabled = false;
    } else {
        button.disabled = false;
    }
}