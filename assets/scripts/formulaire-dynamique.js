const addFormToCollection = (e) => {
    // Récupère les données de la <div> possédant les attributs de la collection
    const data = e.currentTarget.dataset;
    // Récupère la <div> de la collection
    const link = document.querySelector('#' + data.collectionHolderClass);
    // Récupère la <div> qui contiendra les nouveaux champs de la collection
    const div = link.querySelector("#form-dynamique-contenu");
    // Récupère l'index du champ de la collection
    const index = link.dataset.index;

    // Création de l'élément HTML qui sera ajouté dans la <div> de la collection
    const item = document.createElement('html');

    // Définit les valeurs
    item.innerHTML = link
        .dataset
        .prototype
        .replace(
            /__name__/g,
            index
        );

    // Création du bouton de suppression d'un champ de la collection + définit ses valeurs
    let boutonSuppr = document.createElement("button");
    boutonSuppr.type = "button";
    boutonSuppr.className = "btn btn-danger";
    boutonSuppr.id = "delete-allergies-" + index;
    boutonSuppr.innerText = "Supprimer";
    boutonSuppr.addEventListener("click", function(){
        this.previousElementSibling.parentElement.remove();
    });
    // Récupère la <div> du champ de la collection qui vient d'être créé + ajout du bouton supprimer dans cette div
    let newForm = item.querySelector("div");
    // Incrémentation de la valeur de l'index
    link.dataset.index++;
    // Récupère et ajoute le bouton d'ajout à la <div> des nouveaux champs de la collection
    div.append(newForm);
};

/* Supprime le(s) champ(s) de saisie d'une allergie en cas d'erreur de validation du formulaire */
global.removeBtn = function removeBtn(e) {
    // Supprime le champ et son bouton
    e.previousElementSibling.parentElement.parentElement.remove();
    e.remove();
}

// Ajout de l'évènement onClick aux boutons "Ajouter"
document
    .querySelector('#btn-ajout').addEventListener("click", addFormToCollection);