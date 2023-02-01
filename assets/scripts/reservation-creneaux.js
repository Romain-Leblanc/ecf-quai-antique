// Définit les élements du DOM nécessaire
let dateTimeInput = $('#reservation_date');
let listeCreneaux = $('#reservation_heure');
let btnSubmit = $('#btn-submit');
let pNombreCouverts = $('#nombre-couvert');

// Réinitialise les valeurs des champs "date" et des créneaux disponible en cas d'erreur de validation du formulaire
listeCreneaux.empty();
dateTimeInput[0].value = "";

// Appel des fonctions pour récupérer le nombre de convives encore acceptés pour ce jour
setInterval(function () { getNombreReservation(dateTimeInput[0].value); }, 1250);

// Récupère et affiche les créneaux de la date du <input type="date">
global.getCreneauFromDate = function getCreneauFromDate(dateTime) {
    // Si l'élément <p> du DOM affichant le nombre de convives encore acceptés n'est pas affiché, on l'affiche
    if (pNombreCouverts.hasClass("cacher")) {
        pNombreCouverts.removeClass("cacher");
    }
    // Récupère la <div> qui contient la présence d'un message à l'utilisateur ou non
    let divMessage = $('#message-creneaux');
    // Récupère la <div> globale des créneaux
    let divCreneaux = $('#champ-creneaux');
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
                    // Supprime le message et ajoute la liste des créneaux à l'élément du DOM
                    divMessage.remove();
                    listeCreneaux.empty().append(liste);
                    getNombreReservation(dateTime);
                }
                else {
                    // Si le tableau est vide, on affiche un message
                    pMessage.innerText = "Aucun créneau de réservation disponible ce jour.";
                    divMessage.append(pMessage);
                    divCreneaux.append(divMessage);
                    // Vide la liste des créneaux et ajoute le message à l'élément parent du DOM
                    listeCreneaux.empty();
                    pNombreCouverts.addClass("cacher");
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
        success: function(html) {
            if (html === 0 || html === false || html === "") {
                // Désactive le bouton de validation du formulaire si aucun convive encore accepté
                // et efface la valeur du nombre de convives encore acceptés
                disableSubmitButton();
                $('#nombre-couverts-disponible').empty();
                pNombreCouverts.addClass("cacher");
            }
            else {
                enableSubmitButton();
                // Ajoute la valeur du nombre de convives encore acceptés
                $('#nombre-couverts-disponible').empty().append(html);
            }
        }
    });
}

/* Désactive le bouton du formulaire */
function disableSubmitButton() {
    btnSubmit.prop('disabled', true);
}

/* Active le bouton du formulaire */
global.enableSubmitButton = function enableSubmitButton() {
    btnSubmit.prop('disabled', false);
}