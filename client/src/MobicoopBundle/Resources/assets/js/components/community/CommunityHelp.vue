<template>
  <v-card>
    <v-tabs vertical>
      <v-tab>
        Qu'est-ce qu'une communauté?
      </v-tab>
      <v-tab>
        Une communauté, pour quoi faire?
      </v-tab>
      <v-tab>
        Qui peut créer une communauté?
      </v-tab>

      <v-tab-item>
        <v-card flat>
          <v-card-text>
            <p>
              Une communauté est un regroupement d’inscrits ayant un intérêt en commun.
              Elle permet par exemple d’identifier les salariés d’une entreprise, d’une zone d’activités,
              les personnes fréquentant une salle de spectacle
              ou encore les habitants d’une commune, pour faciliter les mises en relation des
              covoitureurs.
            </p>
          </v-card-text>
        </v-card>
      </v-tab-item>
      <v-tab-item>
        <v-card flat>
          <v-card-text>
            <div>
              La création d’une communauté permet :
            </div>
            <br>
            <ul>
              <li>- à ses membres de se voir mutuellement et d’échanger entre eux</li>
              <li>
                - de faciliter la mise en relation des covoitureurs en augmentant la confiance entre
                membres
              </li>
              <li>- de disposer d’un widget personnalisé à installer par exemple sur un intranet</li>
              <li>
                - de disposer, pour le créateur de la communauté, de statistiques sur les inscrits et
                les
                covoitureurs de sa communauté
              </li>
            </ul>
          </v-card-text>
        </v-card>
      </v-tab-item>
      <v-tab-item>
        <v-card flat>
          <v-card-text>
            <p>
              Toute personne qui le souhaite peut créer une communauté dans OuestGo à condition qu’elle soit
              inscrite sur le site et ait envie d’animer et de dynamiser le covoiturage à son niveau. Créer une
              communauté se fait en quelques clics sur ouestgo.fr. Cela vous permettra d’accéder au front office
              librement. Si vous souhaitez aller plus loin dans l’animation et accéder au back office, contactez
              xxxxx@megalis.xxx. L’ensemble des fonctionnalités d’une communauté est accessible gratuitement.
            </p>
          </v-card-text>
        </v-card>
      </v-tab-item>
    </v-tabs>
  </v-card>
</template>
<script>

import moment from "moment";
import {merge} from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/home/HomeSearch.json";
import TranslationsClient from "@clientTranslations/components/home/HomeSearch.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  computed: {
    // creation of the url to call
    urlToCall() {
      return `${this.baseUrl}/${this.route}/${this.origin.addressLocality}/${this.destination.addressLocality}/${this.origin.latitude}/${this.origin.longitude}/${this.destination.latitude}/${this.destination.longitude}/${this.computedDateFormat}/resultats`;
    },
    searchUnavailable() {
      return (!this.origin || !this.destination || this.loading == true)
    },
    computedDateFormat() {
      moment.locale(this.locale);
      return this.date
        ? moment(this.date).format(this.$t("ui.i18n.date.format.fullNumericDate"))
        : moment(new Date()).format(this.$t("ui.i18n.date.format.fullNumericDate"));
    }
  }
}
</script>