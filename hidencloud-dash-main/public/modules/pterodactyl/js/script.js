function confirmModal(message) {
    return new Promise((resolve) => {
        const modalBtn = document.getElementById('confirm-modal-btn');
        const messageElement = document.getElementById('confirm-message-element');
        const confirmBtn = document.getElementById('confirm-btn-submit');
        messageElement.textContent = message;
        modalBtn.click();
        confirmBtn.addEventListener('click', () => {
            resolve(true);
        });
    });
}

function generateCron(selector = 'every-day') {
    console.log(selector)
    let minute = document.getElementById("minute");
    let hour = document.getElementById("hour");
    let day_of_month = document.getElementById("day_of_month");
    let month = document.getElementById("month");
    let day_of_week = document.getElementById("day_of_week");

    switch (selector) {
        case 'every-minute':
            minute.value = '*';
            hour.value = '*';
            day_of_month.value = '*';
            month.value = '*';
            day_of_week.value = '*';
            break;
        case 'every-5-minutes':
            minute.value = '*/5';
            hour.value = '*';
            day_of_month.value = '*';
            month.value = '*';
            day_of_week.value = '*';
            break;
        case 'every-hour':
            minute.value = '0';
            hour.value = '*';
            day_of_month.value = '*';
            month.value = '*';
            day_of_week.value = '*';
            break;
        case 'every-day':
            minute.value = '0';
            hour.value = '0';
            day_of_month.value = '*';
            month.value = '*';
            day_of_week.value = '*';
            break;
        case 'every-week':
            minute.value = '0';
            hour.value = '0';
            day_of_month.value = '*';
            month.value = '*';
            day_of_week.value = '1';
            break;
        case 'every-month':
            minute.value = '0';
            hour.value = '0';
            day_of_month.value = '1';
            month.value = '*';
            day_of_week.value = '*';
            break;
        case '15th-of-month':
            minute.value = '0';
            hour.value = '0';
            day_of_month.value = '15';
            month.value = '*';
            day_of_week.value = '*';
            break;
        case 'every-year':
            minute.value = '0';
            hour.value = '0';
            day_of_month.value = '1';
            month.value = '1';
            day_of_week.value = '*';
            break;
        case 'new-year':
            minute.value = '0';
            hour.value = '0';
            day_of_month.value = '1';
            month.value = '1';
            day_of_week.value = '*';
            break;
        default:
            minute.value = '*/5';
            hour.value = '*';
            day_of_month.value = '*';
            month.value = '*';
            day_of_week.value = '*';
            break;
    }
}
