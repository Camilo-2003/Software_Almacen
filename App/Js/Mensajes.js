function showFloatingMessage(message, isError = false) {
    let messageDiv = document.getElementById('floatingMessage');
    if (!messageDiv) {
        messageDiv = document.createElement('div');
        messageDiv.id = 'floatingMessage';
        document.body.appendChild(messageDiv);
    }

    messageDiv.textContent = message;
    messageDiv.className = 'floating-message';
    messageDiv.classList.add(isError ? 'error' : 'success');

    messageDiv.style.display = 'block';
    messageDiv.style.opacity = '1';

    setTimeout(() => {
        messageDiv.style.opacity = '0';
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 400);
    }, 4000);
}
