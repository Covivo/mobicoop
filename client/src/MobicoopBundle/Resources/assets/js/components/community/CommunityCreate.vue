<template>
  <div id="community_create_root">
    <v-content>
      <v-container
        id="scroll-target"
        style="max-height: 700px"
        class="overflow-y-auto"
        fluid
      >
        <v-row
          justify="center"
          align="center"
        >
          <v-col
            cols="4"
            align="center"
          >
            <!--STEP 1-->
            <v-form
              ref="step 1"
              v-model="step1"
            >
              <v-text-field
                id="name"
                v-model="form.name"
                :rules="form.nameRules"
                :label="$t('models.community.name.placeholder')+` *`"
                name="fullDescription"
                required
              />
              <v-textarea
                id="description"
                v-model="form.description"
                :rules="form.descriptionRules"
                :label="$t('models.community.description.placeholder')+` *`"
                name="description"
                required
              />
              <v-textarea
                id="fullDescription"
                v-model="form.fullDescription"
                :rules="form.fullDescriptionRules"
                :label="$t('models.community.fullDescription.placeholder')+` *`"
                name="fullDescription"
                required
              />
              <v-switch
                v-model="form.private"
                :label="$t('models.community.private.placeholder')"
                name="private"
                @keypress="isNumber(event)"
              />
              <v-btn
                ref="button"
                class="my-13"
                color="success"
                :disabled="!step1"
                @click="poster(form)"
              >
                {{ $t('ui.button.submit') }}
              </v-btn>
            </v-form>
          </v-col>
        </v-row>
      </v-container>
    </v-content>
  </div>
</template>

<script>

import moment from "moment";
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/community/CommunityCreate.json";
import TranslationsClient from "@clientTranslations/components/community/CommunityCreate.json";
import axios from "axios";

let TranslationsMerged = merge(Translations, TranslationsClient,CommonTranslations);
export default {
  name: "CommunityCreate",
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  props:{
    user: {
      type: Array,
      default: null
    },
    community: {
      type: Array,
      default: null
    },
    sentToken: {
      type: String,
      default: null
    }
  },
  data: function () {
    return {
      //
      event: null,

      //step validators
      step1: true,


      //scrolling data
      type: 'selector',
      selected: null,
      duration: 1000,
      offset: 180,
      easing: "easeOutQuad",
      container: "scroll-target",

      form:{
        createToken: this.sentToken,
        fullDescription: null,
        fullDescriptionRules: [
          v => !!v || this.$t("models.community.fullDescription.errors.required"),
        ],
        name: null,
        nameRules: [
          v => !!v || this.$t("models.community.name.errors.required"),
        ],
        description: null,
        descriptionRules: [
          v => !!v || this.$t("models.community.description.errors.required"),
        ],
        private: null
      }
    };
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
  },
  methods:{
    poster: function(form){
      axios.post("/creer/communaute",form)
    }
  }
}
</script>

<style scoped>

</style>