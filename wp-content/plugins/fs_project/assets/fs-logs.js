document.addEventListener('DOMContentLoaded', function () {
    const logsBody = document.getElementById('fs-project-logs-body');
    const ajaxUrl = fsLogsData.ajax_url;

    fetch(`${ajaxUrl}?action=fs_get_logs`)
        .then((response) => response.json())
        .then((logs) => {
            logsBody.innerHTML = logs
                .map(
                    (log) => `
                <tr id="log-${log.id}">
                    <td>${log.id}</td>
                    <td>${log.login_id}</td>
                    <td>${log.post_id}</td>
                    <td>${log.post_title}</td>
                    <td><a href="${log.post_link}" target="_blank">Link</a></td>
                    <td>${log.share_date}</td>
                    <td><button class="delete-log" data-id="${log.id}">Delete</button></td>
                </tr>`
                )
                .join('');
        });

    logsBody.addEventListener('click', function (e) {
        if (e.target.classList.contains('delete-log')) {
            const logId = e.target.getAttribute('data-id');
            fetch(`${ajaxUrl}?action=fs_delete_user_log`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `log_id=${logId}`,
            })
                .then((response) => response.json())
                .then((result) => {
                    if (result.success) {
                        document.getElementById(`log-${logId}`).remove();
                    } else {
                        alert('Could not delete log');
                    }
                });
        }
    });
});