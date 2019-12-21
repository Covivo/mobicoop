export default {
  fr: {
    ui: {
      common: {
        yes: "Oui",
        no: "Non",
        ok: "OK"
      },
      button: {
        previous: "Précédent",
        next: "Suivant",
        register: "Je m'inscris",
        save: "Enregistrer",
        connection: "Se connecter"
      }, 
      form: {
        errors: "Merci de corriger les erreurs suivantes ",
        enterMessage: "Saisissez un message",
        optional: "facultatif"
      },
      infos: {
        misc: {
          at: "à"
        },
        notAvailableYet: "Cette fonction sera bientôt disponible"
      },
      pages: {
        home: {
                        
        },
        signup: {
          chart: {
            text: "J'accepte la charte de la plateforme et sa ",
            link: "politique de protection des données",
            route: "/cgu",
            errors: {
              required: "La validation de la charte est requise"
            }
          }
        }
      },
      i18n: {
        date: {
          format: {
            fullDate: "ddd Do MMMM YYYY",
            fullNumericDate: "YMMDDHHmmss",
            shortDate: "ddd DD/MM",
            urlDate: "YMMDD",
            shortCompleteDate: "DD/MM/YYYY"
          }
        },
        time: {
          format: {
            hourMinute: "HH[h]mm"
          }
        }
      },
      abbr: {
        day: {
          mon: "L",
          tue: "M",
          wed: "Me",
          thu: "J",
          fri: "V",
          sat: "S",
          sun: "D"
        }
      }
    },
    models: {
      user: {
        civility: {
          label: "Civilité",
          placeholder: "Civilité"
        },
        givenName: {
          label: "Prénom",
          placeholder: "Prénom",
          errors: {
            required: "Le prénom est requis"
          }
        },
        familyName: {
          label: "Nom",
          placeholder: "Nom",
          errors: {
            required: "Le nom est requis"
          }
        },
        gender: {
          label: "Civilité",
          placeholder: "Civilité",
          values: {
            male: "Monsieur",
            female: "Madame",
            other: "Autre"
          },
          errors: {
            required: "La civilité est requise"
          }
        },
        birthYear: {
          label: "Année de naissance",
          placeholder: "Année de naissance",
          errors: {
            required: "L'année de naissance est requise"
          }
        },
        birthDay: {
          label: "Date de naissance",
          placeholder: "Date de naissance",
          errors: {
            required: "La date de naissance est requise",
            notadult: "Vous devez avoir 18 ans pour vous inscrire sur la plateforme"
          }
        },
        email: {
          label: "Email",
          placeholder: "Email",
          errors: {
            required: "L'adresse email est requise",
            valid: "L'adresse email doit être valide"
          }
        },
        phone: {
          label: "Téléphone portable",
          placeholder: "Téléphone portable",
          errors: {
            required: "Le numéro de téléphone est requis",
            valid: "Le numéro de téléphone doit être valide"
          }
        },
        password: {
          label: "Mot de passe",
          placeholder: "Mot de passe",
          errors: {
            required: "Le mot de passe est requis",
            min: "Le mot de passe doit comprendre 8 caractères min.",
            upper: "Le mot de passe doit comprendre 1 majuscule.",
            lower: "Le mot de passe doit comprendre 1 minuscule.",
            number : "Le mot de passe doit comprendre 1 chiffre.",
          }
        },
        passwordRepeat: {
          label: "Mot de passe (confirmation)",
          placeholder: "Mot de passe (confirmation)",
          errors: {
            required: "Veuillez confirmer le mot de passe"
          }
        },
        homeTown: {
          label: "Commune de résidence",
          placeholder: "Commune de résidence",
          hint: "Cette information n’est pas obligatoire, elle permet d’être tenu informé des animations territoriales liées à la mobilité et à votre plateforme près de chez vous.",
          required: {
            hint: "Information permettant de rattacher votre inscription à votre collectivité de référence, à des fins statistiques et d'animation."
          },
          errors: {
            required: "La ville de résidence est requise"
          }
        }
      }
    }
  },
  en: {
    ui: {
      common: {
        yes: "Yes",
        no: "No",
        ok: "OK"
      },
      button: {
        previous: "",
        next: "",
        register: "",
        save: "",
        connection: ""
      },
      form: {
        errors: "",
        enterMessage: "",
        optional: ""
      },
      infos: {
        misc: {
          at: ""
        },
        notAvailableYet: ""
      },
      pages: {
        home: {

        },
        signup: {
          chart: {
            text: "",
            link: "",
            route: "",
            errors: {
              required: ""
            }
          }
        }
      },
      i18n: {
        date: {
          format: {
            fullDate: "",
            fullNumericDate: "",
            shortDate: "",
            urlDate: ""
          }
        },
        time: {
          format: {
            hourMinute: ""
          }
        }
      },
      abbr: {
        day: {
          mon: "",
          tue: "",
          wed: "",
          thu: "",
          fri: "",
          sat: "",
          sun: ""
        }
      }
    },
    models: {
      user: {
        givenName: {
          label: "",
          placeholder: "",
          errors: {
            required: ""
          }
        },
        familyName: {
          label: "",
          placeholder: "",
          errors: {
            required: ""
          }
        },
        gender: {
          label: "",
          placeholder: "",
          values: {
            male: "",
            female: "",
            other: ""
          },
          errors: {
            required: ""
          }
        },
        birthYear: {
          label: "",
          placeholder: "",
          errors: {
            required: ""
          }
        },
        email: {
          label: "",
          placeholder: "",
          errors: {
            required: "",
            valid: ""
          }
        },
        phone: {
          label: "",
          placeholder: "",
          errors: {
            required: "",
            valid: ""
          }
        },
        password: {
          label: "",
          placeholder: "",
          errors: {
            required: "",
            min: "",
            minu: "",
            maj: "",
            numb : "",
          }
        },
        passwordRepeat: {
          label: "",
          placeholder: "",
          errors: {
            required: ""
          }
        },
        homeTown: {
          label: "",
          placeholder: "",
          hint: "",
          errors: {
            required: ""
          }
        }
      }
    }
  }
}