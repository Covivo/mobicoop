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
            :src="$t('externalRoute')"
            width="100%"
            height="440px"
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
            <h4>{{ $t('textDetails.title') }}</h4>
            <p
              class="mt-8"
              v-html="$t('textDetails.p1')"
            />
            <p v-html="$t('textDetails.p2', {'url':getUrl()})" />
            <p v-html="$t('textDetails.p3')" />
          </v-row>
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>
<script>

import axios from "axios";
import Translations from "@translations/components/utilities/PlatformWidget.json";


export default {
  components: {
  },
  i18n: {
    messages: Translations,
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
      errorUpdate: false
    }
  },
  methods:{
    getUrl() {
      return window.location.protocol +"//"+ window.location.host + this.$t('externalRoute');
    }
  }
}
</script>
