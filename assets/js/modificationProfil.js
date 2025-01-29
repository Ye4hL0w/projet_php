document.addEventListener('DOMContentLoaded', function() {
    var btnModifierProfil = document.getElementById('btnModifierProfil');
    var formModifierProfil = document.getElementById('formModifierProfil');

    if(btnModifierProfil && formModifierProfil) {
        btnModifierProfil.addEventListener('click', function() {
            if (formModifierProfil.style.display === "none") {
                formModifierProfil.style.display = "block";
            } else {
                formModifierProfil.style.display = "none";
            }
        });
    }
});