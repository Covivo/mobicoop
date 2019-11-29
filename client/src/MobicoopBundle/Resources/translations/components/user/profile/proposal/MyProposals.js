export default {
  fr: {
    proposals: {
      ongoing: "Annonces en cours",
      archived: "Annonces archivées",
      driver: "Je serai conducteur",
      passenger:"Je serai passager",
      diverOrPassenger:"Peur importe: je serai conducteur ou passager"
    },
    delete: {
      route: "/covoiturage/annonce/supprimer",
      success: "Votre annonce a été supprimée avec succès.",
      error: "Une erreur est survenue lors de la suppression de votre annonce.",
      dialog: {
        base: {
          title: "Etes-vous sur de vouloir supprimer cette annonce ?",
          text: "Toute suppression d'annonce est définitive, êtes-vous sur de vouloir supprimer cette annonce ?"
        },
        accepted: {
          title: "Il existe au moins un covoiturage planifié pour cette annonce",
          text: "Un ou plusieurs covoiturages ont déjà été planifiés en lien avec cette annonce. Votre suppression annulera donc ces covoiturages acceptés. " +
            "Si vous confirmez votre suppression, quel message voulez-vous envoyer aux covoitureurs avec qui vous annulez ?"
        },
        pending: {
          title: "Il existe au moins une demande pour cette annonce",
          text: "Une ou plusieurs demandes ont déjà été faites en lien avec cette annonce. Votre suppression rejettera donc toutes les demandes en cours. " +
            "Si vous confirmez votre suppression, quel message voulez-vous envoyer aux covoitureurs dont vous rejetez les demandes ?",
        },
        cancel: "Annuler",
        validate: "Confirmer"
      }
    }
  },
  en: {
    proposals: {
      ongoing: "On going proposals",
      archived: "Archived proposals"
    },
    delete: {
      route: "/carpool/ad/delete",
      success: "Your ad had been deleted with success.",
      error: "An error happened."
    }
  }
}