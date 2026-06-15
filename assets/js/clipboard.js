function iniciarBotaoCopiar() {
    const copyButton = document.getElementById('btnCopyUrl');

    if (copyButton) {
        copyButton.onclick = function() {
            const copyText = document.getElementById('recoveryUrlInput');
            copyText.select();
            copyText.setSelectionRange(0, 99999);

            navigator.clipboard.writeText(copyText.value).then(function() {
                const btnText = document.getElementById("btnCopyText");
                btnText.innerText = "Copied!";
                copyButton.classList.replace("btn-dark", "btn-success");

                setTimeout(function() {
                    btnText.innerText = "Copy";
                    copyButton.classList.replace("btn-success", "btn-dark");
                }, 2000);
            }).catch(function (err) {
                console.error("Erro na cópia:", err);
                alert("Failed to copy the link, please select and copy it manually.");
            });
        };
    }
}

iniciarBotaoCopiar();

document.addEventListener('DOMContentLoaded', iniciarBotaoCopiar);

document.addEventListener('turbo:load', iniciarBotaoCopiar);
