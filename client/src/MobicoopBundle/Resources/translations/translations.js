export default {
  fr: {
    ui: {
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
            chartValid: "Je valide la charte",
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
            urlDate: "YMMDD"
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
        email: {
          label: "Email",
          placeholder: "Email",
          errors: {
            required: "L'adresse email est requise",
            valid: "L'adresse email doit être valide"
          }
        },
        phone: {
          label: "Téléphone",
          placeholder: "Numéro de téléphone",
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
            chartValid: "",
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