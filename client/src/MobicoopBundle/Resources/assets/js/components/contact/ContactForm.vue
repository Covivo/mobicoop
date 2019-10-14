<template>
  <v-content>
    <v-container fluid>
      <v-row
        justify="center"
      >
        <v-col
          cols="12"
          sm="6"
          md="4"
          align="center"
        >
          <v-form v-model="valid">
            <v-container>
              <v-row>
                <v-col
                  cols="12"
                >
                  <v-text-field
                    v-model="form.lastName"
                    :label="$t('lastName.placeholder')"
                  />
                </v-col>

                <v-col
                  cols="12"
                >
                  <v-text-field
                    v-model="form.firstName"
                    :label="$t('firstName.placeholder')"
                  />
                </v-col>

                <v-col
                  cols="12"
                >
                  <v-text-field
                    v-model="form.email"
                    :rules="form.emailRules"
                    :label="$t('email.placeholder') + ` *`"
                  />
                </v-col>

                <v-col
                  cols="12"
                >
                  <v-select
                    v-model="form.demand"
                    :items="form.demandItems"
                    :label="$t('demand.placeholder')"
                  />
                </v-col>

                <v-col
                  cols="12"
                >
                  <v-textarea
                    v-model="form.message"
                    :rules="form.messageRules"
                    :label="$t('message.placeholder') + ` *`"
                  />
                </v-col>
              </v-row>
            </v-container>
          </v-form>
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>

<script>
import CommonTranslations from "@translations/translations.json";
import {merge} from "lodash";
import Translations from "@translations/components/contact/ContactForm.json";
import TranslationsClient from "@clientTranslations/components/contact/ContactForm.json";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    sharedMessages: CommonTranslations,
    messages: TranslationsMerged
  },
  data () {
    return {
      valid: true,
      form:{
        email: null,
        emailRules: [
          v => !!v || this.$t("email.errors.required"),
          v => /.+@.+/.test(v) || this.$t("email.errors.valid")
        ],
        firstName: null,
        lastName: null,
        demandItems: this.$t("demand.items"),
        demand: null,
        message: null,
        messageRules: [
          v => !!v || this.$t("message.errors.required"),
        ],
      }
    }
  }
}
</script>

<style scoped>

</style>
