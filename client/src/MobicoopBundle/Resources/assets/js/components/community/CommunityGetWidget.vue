<template>
  <v-content>
    <!--SnackBar-->
    <v-snackbar
      v-model="snackbar"
      :color="(errorUpdate)?'error':'warning'"
      top
    >
      <v-btn
        color="white"
        text
        @click="snackbar = false"
      >
        <v-icon>mdi-close-circle-outline</v-icon>
      </v-btn>
    </v-snackbar>

    <v-container>
      <v-row
        justify="center"
      >
        <v-col
          cols="4"
          md="5"
          align="center"
          class="justify-center"
        >
          <iframe
            :src="$t('widget.externalRoute', {'id':community.id})"
            width="100%"
            height="640px"
            frameborder="0"
            scrolling="no"
          />
        </v-col>
        <v-col
          cols="8"
          md="7"
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
import Translations from "@translations/components/community/Community.json";
import TranslationsClient from "@clientTranslations/components/community/Community.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  components: {
  },
  i18n: {
    messages: TranslationsMerged,
  },
  props:{
    community:{
      type: Object,
      default: null
    },
  },
  data () {
    return {
      search: '',
      loading: false,
      snackbar: false,
      errorUpdate: false,
      isLogged: false
    }
  },
  methods:{
    getUrl() {
      return window.location.protocol +"//"+ window.location.host + this.$t('widget.externalRoute', {'id':this.community.id});
    }
  }
}
</script>
