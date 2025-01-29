document.addEventListener("DOMContentLoaded", function () {
    const masquerButtons = document.querySelectorAll(".masquer-btn");

    masquerButtons.forEach(button => {
        button.addEventListener("click", function (e) {
            e.preventDefault();

            const publicationId = this.getAttribute("data-publication-id");

            // Effectuez une requête AJAX pour mettre à jour la base de données
            fetch(`masquer_publication.php?id=${publicationId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Mettez à jour le libellé du bouton et la classe CSS
                        if (data.masquer) {
                            this.textContent = "Rendre Visible";
                            this.classList.remove("masquer-btn");
                            this.classList.add("rendre-visible-btn");
                        } else {
                            this.textContent = "Masquer";
                            this.classList.remove("rendre-visible-btn");
                            this.classList.add("masquer-btn");
                        }
                    } else {
                        alert("Erreur lors de la mise à jour de la publication.");
                    }
                })
                .catch(error => {
                    console.error("Erreur lors de la requête AJAX : ", error);
                });
        });
    });
});