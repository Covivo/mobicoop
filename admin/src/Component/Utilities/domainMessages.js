import UserReferenceField from "../User/UserReferenceField";
import React from "react";

const fr = {
    custom: {
        email: {
            texte: {
                emailTous: 'Email à tous',
                emailSelect: 'Email aux sélectionnés',
                blockUnsubscribe :"Vous ne pouvez pas envoyer de mailing à des personnes qui l'ont refusé."
            }
        },
        rgpd : {
            modal :{
                titre : 'Avertissement RGPD',
                texte : 'Envoyer des emails en masse aux utilisateurs de %{instanceName} engage votre responsabilité, et a un impact potentiel sur toute la plate-forme. Pour rester conformes à la Réglementation Générale sur la Protection des Données, les emails envoyés doivent rester dans le cadre d"actualité de service" et en aucun cas inciter à l\'usage d\'autres services que %{instanceName} . \n' +
                        'Par ailleurs, le droit de retrait des utilisateurs ne peut s\'exercer par eux de manière distincte entre les différents expéditeurs : si un utilisateur désapprouve un email envoyé par un administrateur territorial ou un référent de commuauté et se désinscrit, il ne pourra plus être destinataire de tout futur envoi en masse, quelque soit l\'expéditeur.\n' +
                        'Une grande vigilance est donc demandée à chacun sur la pertinence et la fréquence de ses envois en masse.',
                buttonAgree : "Je comprend et j'accepte",
                buttonDisagree : "Je refuse"
            },
        },
        label : {
            event :{
                name : 'Nom',
                resume : 'Résumé',
                resumefull : 'Résumé long',
                site : 'Site internet',
                adresse : 'Adresse',
                setTime : "Préciser l'heure",
                createur : "Créateur",
                status : "Status",
                dateTimeStart: "Date et heure de début",
                dateTimeFin : "Date et heure de fin",
                dateStart: "Date de début",
                dateFin: "Date de fin",
                image : "Image"
            },
            community :{
                community : 'Communauté',
                name : 'Nom',
                numberMember: 'Nombre de membres ',
                adress : 'Adresse',
                memberHidden : 'Membres masqués',
                proposalHidden : 'Annonces masquées',
                validationType : 'Type de validation',
                domainName : 'Nom de domaine',
                description : 'Description',
                descriptionFull : 'Description complète',
                createdDate : 'Date de création',
                updateDate : 'Date de mise à jour',
                status : "Status",
                createdBy : 'Référent',
                oldAdress : 'Ancienne adresse',
                newAdress : 'Nouvelle adresse',
                members : 'Membres',
                member : 'Membre',
                detail : 'Détail',
                membersModerator : 'Membres & Modérateurs',
                joinAt : 'Rejoint le',
                acceptedAt : 'Accepté le',
                refusedAt : 'Refusé le',
            },
            campaign : {
                object : 'Sujet',
                numberMember: 'Nombre de destinataires ',
                sender: 'Expéditeur',
                state: 'Statut',
                createdDate: 'Création',
                updateDate: 'Dernière modification',
                sendDate: 'Expédition',
                statusCampaign : {
                    init : 'Initialiser',
                    create: 'Créé',
                    send: 'Expédié',
                    archive: 'Archivé',
                },
                resumeCampaign : 'Reprendre la campagne'
            }
        }
    }
}
const en = {
    custom: {

    }

}

export {
    fr,en
}
