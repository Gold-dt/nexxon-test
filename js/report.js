

function popup() {
  document.getElementById("reporter").classList.toggle("report-popup-on");
}

document.getElementById('sendWebhookBtn').addEventListener('click', sendWebhook);

        function sendWebhook() {
            const webhookURL = 'https://discord.com/api/webhooks/856856472738856963/Obecft5nTsmDEL9vAh0tY5LJWdVFPhCiRnuHBL9lWqeB4cQee3sm0SiqsV6e4Fikm25w';  // Cseréld le a saját webhook URL-edre
            const xhr = new XMLHttpRequest();
            xhr.open('POST', webhookURL, true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    if (xhr.status === 204) {
                        console.log('Webhook sent successfully!');
                    } else {
                        console.error('Failed to send webhook:', xhr.statusText);
                    }
                }
            };

            const embed = {
                title: "Webadmin Report",
                description: "**Leírás: **\nThis is a test embed message from JavaScript!\ns",
                color: 0x00ff00, // Green color in hexadecimal
                fields: [
                    {
                        name: "Ki küldte",
                        value: "Some value here",
                        inline: true
                    },
                    {
                        name: "Kire",
                        value: "Another value here",
                        inline: true
                    }
                ],
                footer: {
                    text: "This is a footer",
                    icon_url: "https://i.imgur.com/AfFp7pu.png" // Opcionális: egyéni avatar kép
                },
                timestamp: new Date()
            };

            const message = JSON.stringify({
                
                
                embeds: [embed]
            });

            xhr.send(message);
        }