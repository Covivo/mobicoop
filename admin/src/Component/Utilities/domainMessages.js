const fr = {
    custom: {
        email: {
            texte: {
                emailTous: 'Email à tous',
                emailSelect: 'Email aux sélectionnés',
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