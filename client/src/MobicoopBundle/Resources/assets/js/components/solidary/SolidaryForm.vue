<template>
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
        <v-alert
          dismissible
          :value="alert.show"
          :type="alert.type"
        >
          <!--Use of span and v-html to handle multiple lines errors if needed-->
          <span v-html="alert.message" />
        </v-alert>
      </v-col>
    </v-row>
    <!--todo: faire un searchSolidary component-->
    <Search />
      
    <v-row
      justify="center"
    >
      <v-col
        cols="12"
        sm="8"
        md="6"
        align="center"
      >
        <v-form
          id="formSolidary"
          ref="form"
          v-model="valid"
          lazy-validation
        >
          <v-container>
            <v-row>
              <v-col
                cols="12"
              >
                <v-select
                  :items="form.civilityItems"
                  :label="$t('models.user.civility.placeholder') + ' *'"
                />
              </v-col>
              <v-col
                cols="12"
              >
                <v-text-field
                  :label="$t('models.user.familyName.placeholder') + ' *'"
                  name="lastName"
                />
              </v-col>
              <v-col
                cols="12"
              >
                <v-text-field
                  :label="$t('models.user.givenName.placeholder') + ' *'"
                  name="firstName"
                />
              </v-col>
              
              <v-col
                cols="12"
              >
                <v-menu
                  ref="menu"
                  v-model="pickerActive"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  offset-y
                  min-width="290px"
                >
                  <template v-slot:activator="{ on }">
                    <v-text-field
                      v-model="getYearOfBirth"
                      :label="$t('yearsOfBirth.placeholder') + ' *'"
                      v-on="on"
                    />
                  </template>
                  <v-date-picker
                    ref="picker"
                    v-model="form.yearsOfBirth"
                    no-title
                    reactive
                    :max="years.max"
                    :min="years.min"
                    @input="save"
                  >
                    <v-spacer />
                    <v-btn
                      text
                      color="primary"
                      @click="menu = false"
                    >
                      {{ $t('ui.buttons.cancel') }}
                    </v-btn>
                    <v-btn
                      text
                      color="primary"
                      @click="$refs.menu.save(form.yearsOfBirth)"
                    >
                      {{ $t('ui.buttons.validate') }}
                    </v-btn>
                  </v-date-picker>
                </v-menu>
              </v-col>
              <v-col
                cols="12"
              >
                <v-text-field
                  :label="$t('models.user.email.placeholder') + ' *'"
                  name="email"
                />
              </v-col>
              <v-col
                cols="12"
              >
                <v-text-field
                  :label="$t('models.user.phone.placeholder') + ' *'"
                  name="phone"
                />
              </v-col>
              <v-col
                cols="12"
              >
                <v-switch
                  v-model="form.hasRSA"
                  :label="$t('hasRSA.placeholder')"
                />
              </v-col>
            </v-row>
          </v-container>
        </v-form>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import {merge} from "lodash";
import moment from "moment";
import Translations from "@translations/components/solidary/SolidaryForm.json";
import TranslationsClient from "@clientTranslations/components/solidary/SolidaryForm.json";
import Search from "@components/carpool/search/Search.vue";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged
  },
  name: "SolidarityForm",
  components: {
    Search
  },
  data () {
    return {
      valid: false,
      alert: {
        type: "success",
        show: false,
        message: ""
      },
      pickerActive: false,
      // todo: faire une fichier de fonction pour les rules
      form: {
        civility: "",
        civilityItems: [],
        givenName: "",
        familyName: "",
        email: "",
        emailRules: [
          v => !!v || this.$t("email.errors.required"),
          v => /.+@.+/.test(v) || this.$t("email.errors.valid")
        ],
        phoneNumer: null,
        yearsOfBirth: null,
        hasRSA: false,
        
      },
      years: {
        max: moment().format(),
        min: moment().subtract(100, 'years').format()
      }
    }
  },
  computed: {
    getYearOfBirth() {
      return this.form.yearsOfBirth ? moment(this.form.yearsOfBirth).format('YYYY') : null
    }
  },
  watch: {
    pickerActive(val) {
      val && this.$nextTick(() => (this.$refs.picker.activePicker = 'YEAR'))
    },
  },
  methods: {
    save (date) {
      this.$refs.menu.save(date);
      this.$refs.picker.activePicker = 'YEAR';
      this.menu = false;
    }
  }
}
</script>

<style scoped>

</style>