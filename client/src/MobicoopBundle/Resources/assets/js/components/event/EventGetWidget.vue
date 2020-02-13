<template>
  <v-content>
    <!--SnackBar-->
    <v-snackbar
      v-model="snackbar"
      :color="(errorUpdate)?'error':'warning'"
      top
    >
      <!--      {{ (errorUpdate)?textSnackError:textSnackOk }}-->
      <v-btn
        color="white"
        text
        @click="snackbar = false"
      >
        <v-icon>mdi-close-circle-outline</v-icon>
      </v-btn>
    </v-snackbar>

    <v-container>
      <!-- eventGetWidget buttons and map -->
      <v-row
        justify="center"
      >
        <v-col
          cols="4"
          align="center"
          class="justify-center"
        >
          <iframe
            :src="`/evenement-widget/${event.id}`"
            width="100%"
            height="640px"
            frameborder="0"
            scrolling="no"
          />
        </v-col>
        <v-col
          cols="8"
          class="mt-12"
        >
          <v-row class="mt-12">
            <h4>Intégrer le widget</h4>
            <p class="mt-8">
              Pour intégrer le widget, il faut copier le texte ci-dessous et le coller sur votre site web.<br>
              Vous pouvez modifier les éléments en gras afin de personnaliser votre widget.
            </p>
            <p>
              &lt;iframe src="{{ getUrl() }}" width="<strong>100%</strong>" height="<strong>440px</strong>" frameborder="0" scrolling="no"&gt;&lt;/iframe&gt;
            </p>
            <p><strong>Attention</strong> : Certains outils de publication comme Wordpress nécessitent l'ajout de plugins spécifiques pour pouvoir utiliser une iFrame.</p>
          </v-row>
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>
<script>

import axios from "axios";
import { merge } from "lodash";
import Translations from "@translations/components/event/Event.json";
import TranslationsClient from "@clientTranslations/components/event/Event.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  components: {
  },
  i18n: {
    messages: TranslationsMerged,
  },
  props:{
    user: {
      type: Object,
      default: null
    },
    geodata: {
      type: Object,
      default: null
    },
    users: {
      type: Array,
      default: null
    },
    event:{
      type: Object,
      default: null
    },
    avatarVersion: {
      type: String,
      default: null
    },
    urlAltAvatar: {
      type: String,
      default: null
    },
    regular: {
      type: Boolean,
      default: false
    },
    punctualDateOptional: {
      type: Boolean,
      default: false
    },
  },
  data () {
    return {
      search: '',
      loading: false,
      snackbar: false,
      errorUpdate: false,
      isLogged: false,
      params: { 'eventId' : this.event.id },

    }
  },
  methods:{
    checkIfUserLogged() {
      if (this.user !== null) {
        this.isLogged = true;
      }
    },
    getUrl() {
      return window.location.protocol +"//"+ window.location.host + "/evenement-widget/" + this.event.id;
    }
  }
}
</script>
