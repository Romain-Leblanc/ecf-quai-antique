// Définit les élements du DOM nécessaire
let dateTimeInput = $('#reservation_date');
let listeCreneaux = $('#reservation_heure');
let btnSubmit = $('#btn-submit');
let pNombreCouverts = $('#nombre-couvert');
let divMessage = $('#message-creneaux');
let divCreneaux = $('#champ-creneaux');

// Réinitialise les valeurs des champs "date" et des créneaux disponible en cas d'erreur de validation du formulaire
listeCreneaux.empty();
dateTimeInput[0].value = "";
disableSubmitButton();

// Appel des fonctions pour récupérer le nombre de convives encore acceptés pour ce jour
setInterval(function () { getNombreReservation(dateTimeInput[0].value); }, 1250);

// Récupère et affiche les créneaux de la date du <input type="date">
global.getCreneauFromDate = function getCreneauFromDate(dateTime) {
    // Si l'élément <p> du DOM affichant le nombre de convives encore acceptés n'est pas affiché, on l'affiche
    if (pNombreCouverts.hasClass("cacher")) {
        pNombreCouverts.removeClass("cacher");
    }
    // Vide la liste des créneaux de réservation possible
    listeCreneaux.empty();
    // Définit un élement <p> du DOM
    let pMessage = document.createElement("p");

    // Si un message est affiché, on redéfinit l'élément <p> précédent par celui du message
    if (Object.keys(divMessage).length > 0) {
        pMessage = divMessage.children()[0];
    }
    else {
        // Sinon on définit les valeurs de l'élément <p> pour le message
        pMessage.className = "fst-italic text-center";
        divMessage = document.createElement("div");
        divMessage.id = "message-creneaux";
        divMessage.className = "w-100 d-flex flex-row justify-content-center";
    }

    // Si aucune valeur sélectionnée pour le champ "date" pour la réservation, on affiche un message
    if (dateTime === "") {
        pMessage.innerText = "Veuillez sélectionner une date.";
        divMessage.append(pMessage);
        divCreneaux.append(divMessage);
        pNombreCouverts.addClass("cacher");
        // Désactive le bouton de validation du formulaire si aucun convive encore accepté
        disableSubmitButton();
    }
    else {
        // Transforme la chaine en objet Datetime au format anglais (elle sera traduit dans le contrôleur)
        let dateJour = new Date(dateTime).toLocaleString('en-GB', { weekday: 'long', timeZone: 'Europe/Paris'});
        // Définit le premier caractère en majuscule
        dateJour = dateJour.charAt(0).toUpperCase() + dateJour.slice(1);

        // Affiche un message de chargement
        pMessage.innerText = "Chargement des créneaux...";
        divMessage.append(pMessage);
        divCreneaux.append(divMessage);

        // Requête Ajax pour les modèles de voitures
        $.ajax({
            url : '/reservation',
            type: 'POST',
            data : {'dateJour': dateJour},
            success: function(html) {
                console.log(html);
                let liste = "";
                let cleTableau = 0;
                // Si le tableau renvoyé par la requête n'est pas vide
                if (Object.keys(html.listeCreneaux).length > 0) {
                    // Boucle sur le tableau multidimensionnel
                    Object.entries(html.listeCreneaux).forEach(unHoraire => {
                        // Boucle sur un créneau d'ouverture/fermeture
                        unHoraire.forEach(unCreneau => {
                            // Ajout d'une vérification si c'est bien un objet
                            if (typeof unCreneau == "object") {
                                let valeurs = Object.values(unCreneau);
                                // Boucle sur chaque créneau pour ajouter un <input type="radio"> et son <label>
                                valeurs.forEach((uneHeure) => {
                                    // Je redéfinis exactement les mêmes attributs que ceux par défaut, sinon une erreur de selection d'un nouveau créneau sera généré
                                    liste += "<input type='radio' id='reservation_heure_"+cleTableau+"' required='required' name='reservation[heure]' class='btn-check' value='"+uneHeure+"'>";
                                    liste += "<label for='reservation_heure_"+cleTableau+"' class='required'>"+uneHeure+"</label>";
                                    cleTableau++;
                                });
                            }
                        });
                    });
                    // Supprime le message, ajoute la liste des créneaux à l'élément du DOM et active le bouton
                    divMessage.remove();
                    listeCreneaux.empty().append(liste);
                    enableSubmitButton();
                }
                else {
                    divMessage.remove();
                    // Remplace le message actuel par un nouveau, l'ajoute à l'élément du DOM et active le bouton
                    listeCreneaux.empty();
                    pMessage.innerText = "Aucun créneau de réservation disponible ce jour.";
                    divMessage.append(pMessage);
                    divCreneaux.append(divMessage);
                    pNombreCouverts.addClass("cacher");
                    disableSubmitButton();
                }
            }
        });
    }
}

// Récupère le nombre de convives encore acceptés ce jour
function getNombreReservation(dateTime) {
    $.ajax({
        url : '/reservation/nombre',
        type: 'POST',
        data : {'dateTime': dateTime},
        success: function(nombre) {
            console.log(nombre);
            if (nombre === 0 || nombre === "") {
                console.log("'"+nombre+"'");
                // Désactive le bouton de validation du formulaire si aucun convive encore accepté
                disableSubmitButton();
                // Supprime le message
                divMessage.empty();
                // Vide la liste des créneaux
                listeCreneaux.empty();
                $('#nombre-couverts-disponible').empty();
                // Cache le nombre de couverts restants
                pNombreCouverts.addClass("cacher");
                let pMessage = document.createElement("p");
                // Ajoute un message à la place de la liste des créneaux
                pMessage.innerText = "Aucune nouvelle réservation acceptée ce jour (seuil de convives atteint).";
                pMessage.className = "fst-italic text-center";
                divMessage.append(pMessage);
                divCreneaux.append(divMessage);
            }
            else if(nombre !== false) {
                // Active le bouton de validation du formulaire
                enableSubmitButton();
                // Ajoute la valeur du nombre de convives encore acceptés
                $('#nombre-couverts-disponible').empty().append(nombre);
            }
        }
    });
}

/* Désactive le bouton du formulaire */
function disableSubmitButton() {
    btnSubmit.prop('disabled', true);
}

/* Active le bouton du formulaire */
function enableSubmitButton() {
    btnSubmit.prop('disabled', false);
}