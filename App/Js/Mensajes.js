function showFloatingMessage(message, type = 'success') {
    let messageDiv = document.getElementById('floatingMessage');
    if (!messageDiv) {
        messageDiv = document.createElement('div');
        messageDiv.id = 'floatingMessage';
        document.body.appendChild(messageDiv);
    }
    messageDiv.textContent = message;
    messageDiv.className = 'floating-message';
    messageDiv.classList.add(type); 

    messageDiv.style.display = 'block'; 
    messageDiv.style.opacity = '1';

    setTimeout(() => {
        messageDiv.style.opacity = '0';
        setTimeout(() => {
            messageDiv.style.display = 'none'; 
        }, 500); 
    }, 4000); 
}