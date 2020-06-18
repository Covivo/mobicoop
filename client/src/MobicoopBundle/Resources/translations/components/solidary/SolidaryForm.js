export default {
  fr: {
    ui: {
      buttons: {
        validate: {
          label: "Valider",
          route: "/solidarite/demande"
        },
        cancel: {
          label: "Annuler"
        }
      }
    },
    stepper: {
      origin: "Où habitez-vous ?",
      service: "Est-ce un service pour vous ?",
      yourJourney: "Votre trajet",
      ponctual: "Votre trajet ponctuel",
      regular: "Votre trajet régulier",
      you: "Vous",
      summary: "Récapitulatif"
    },
    origin: {
      placeholder: "Adresse de domicile"
    },
    structure: {
      placeholder: "Structure accompagnante",
      text: "Chaque structure a ses propres critères. Si vous ne remplissez pas les critères pour l'une, peut-être les remplisserez-vous pour une autre qui dessert aussi votre domicile ?",
      title: "Structure accompagnante",
      "route": "/structures/liste-des-communautes",
      mandatoryProofs: "Critères obligatoires",
      optionalProofs: "Informations complémentaires"
    },
    yourJourney: {
      subjectTitle: "Que voulez-vous faire ?",
      subject: "Object du déplacement",
      destinationTitle: "Où faut-il aller ? (si adresse connue)",
      destination: "Lieu de destination (facultatif)",
      regular: {
        question: "Est-ce un trajet régulier ?",
        no: "Non",
        yes: "Oui, je ferai le trajet régulièrement"
      },
      needs: "Autres informations",
      otherInfoTitle: "Autres précisions",
      otherInfo: "Choisissez des éléments ou ajoutez-en",
    },
    frequency: {
      punctual: {
        startDateTitle: "A quelle date souhaitez-vous partir ?",
        startDateChoice1: "Date d'arrivée",
        startDateChoice2: "dans la semaine",
        startDateChoice3: "dans les deux semaines",
        startDateChoice4: "dans le mois"
      },
      regular: {
        days: "Indiquez les jours pour lesquels vous avez besoin d'un trajet régulier"
      },
      startTimeTitle: "A quelle heure souhaitez-vous partir ?",
      startTimeChoice1: "Heure d'arrivée",
      startTimeChoice2: "Entre 8h et 13h",
      startTimeChoice3: "Entre 13h et 18h",
      startTimeChoice4: "Entre 18h et 21h",
      endTimeTitle: "A quelle heure souhaitez-vous revenir ?",
      endTimeChoice1: "Je n'ai pas besoin qu'on me ramène",
      endTimeChoice2: "Heure d'arrivée",
      endTimeChoice3: "Une heure plus tard",
      endTimeChoice4: "Deux heures plus tard",
      endTimeChoice5: "Trois heures plus tard",
    },
    other: {
      label: "Autre",
      placeholder: "Autre"
    },
    birthDate: {
      label: "Date de naissance",
      placeholder: "Date de naissance"
    },
    firstNameText: "Visible pour tous",
    success: "Votre demande de coup de pouce a bien été envoyée !",
  }
}