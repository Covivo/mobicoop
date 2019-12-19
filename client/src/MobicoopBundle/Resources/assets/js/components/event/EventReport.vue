<template>
  <div>
    <!--SnackBar-->
    <v-snackbar
      v-model="isSnackbarOpened"
      :color="(isSent)?'success':'error'"
      top
    >
      {{ (isSent) ? this.$t('snackbar.thankyou') : this.$t('snackbar.error') }}
      <v-btn
        color="white"
        text
        @click="isSnackbarOpened = false"
      >
        <v-icon>mdi-close-circle-outline</v-icon>
      </v-btn>
    </v-snackbar>

    <div v-if="!isSent">
      <v-btn
        color="error"
        rounded
        :loading="isLoading"
        @click="isDialogOpened=true"
      >
        {{ $t('button') }}
      </v-btn>
      <!--Confirmation Popup-->
      <v-dialog
        v-model="isDialogOpened"
        persistent
        max-width="500"
      >
        <v-card>
          <v-card-title class="headline">
            {{ $t('popup.title') }}
          </v-card-title>
          <v-card-text
            v-html="$t('popup.content', {eventName: event.name})"
          />
          <v-card-text>
            <v-form
              ref="form"
              lazy-validation
            >
              <v-text-field
                v-model="email"
                :label="$t('popup.form.email.label')"
                :rules="emailRules"
                required
              />
              <v-textarea
                v-model="description"
                :label="$t('popup.form.description.label')"
                :rules="descriptionRules"
                required
              />
            </v-form>
          </v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn
              color="secondary darken-1"
              text
              @click="isDialogOpened=false"
            >
              {{ $t('popup.button.cancel') }}
            </v-btn>
            <v-btn
              color="error darken-1"
              text
              @click="isDialogOpened=false; report()"
            >
              {{ $t('popup.button.report') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </div>
  </div>
</template>
<script>

import axios from "axios";
import { merge } from "lodash";
import Translations from "@translations/components/event/EventReport.json";
import TranslationsClient from "@clientTranslations/components/event/EventReport.json";
let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
  },
  props:{
    event:{
      type: Object,
      default: null
    }
  },
  data() {
    return {
      isLoading: false,
      isDialogOpened: false,
      isSnackbarOpened: false,
      snackbarText: null,
      isSent: false,
      email: null,
      emailRules: [
        v => !!v || this.$t("popup.form.email.errors.required"),
        v => /.+@.+/.test(v) || this.$t("popup.form.email.errors.valid")
      ],
      description: null,
      descriptionRules: [
        v => !!v || this.$t("popup.form.description.errors.required")
      ]
    }
  },
  created() {
    console.log(this.event);
  },
  methods:{
    report() {
      this.isLoading = true;

      let url = `${this.$t("routes.report", {id: this.event.id})}`;

      // LOAD INPUT
      let params = new FormData();
      params.append("email", this.email);
      params.append("description", this.description);

      axios
        .post(url, params)
        .then(res => {
          if(200 === res.status && res.data.success) {
            this.isSent = true;
          }
          this.isSnackbarOpened = true;
          this.isLoading = false;
        });
    }
  }
}
</script>
