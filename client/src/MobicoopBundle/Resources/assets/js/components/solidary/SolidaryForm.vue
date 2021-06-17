<template>
  <v-container>
    <v-row
      justify="center"
    >
      <v-col
        cols="12"
        sm="6"
        md="4"
        align="center"
      >
        <!--<v-alert-->
        <!--dismissible-->
        <!--:value="alert.show"-->
        <!--:type="alert.type"-->
        <!--&gt;-->
        <!--&lt;!&ndash;Use of span and v-html to handle multiple lines errors if needed&ndash;&gt;-->
        <!--<span v-html="alert.message" />-->
        <!--</v-alert>-->

        <v-snackbar
          v-model="alert.show"
          :color="(alert.type === 'error')?'error':'primary'"
          top
        >
          {{ alert.message }}
          <v-btn
            color="white"
            text
            @click="alert.show = false"
          >
            <v-icon>mdi-close-circle-outline</v-icon>
          </v-btn>
        </v-snackbar>
      </v-col>
    </v-row>

    <v-row
      justify="center"
    >
      <v-col
        cols="12"
        sm="8"
        md="6"
        align="center"
      >
        <!--SearchJourney-->
        
        <search-journey
          :geo-search-url="geoSearchUrl"
          :user="user"
          :init-regular="regular"
          :punctual-date-optional="punctualDateOptional"
          :show-required="true"
          @change="searchChanged"
        />
      </v-col>
    </v-row>
    
    <!--Structure and subject-->
    <v-row
      justify="center"
    >
      <v-col
        cols="6"
        sm="4"
        md="3"
      >
        <v-select
          v-model="form.structure"
          :items="structures"
          item-text="name"
          item-value="id"
          :label="$t('structure.placeholder')"
        />
      </v-col>
      <v-col
        cols="6"
        sm="4"
        md="3"
      >
        <v-text-field
          v-model="form.otherStructure"
          :disabled="!isOtherStructureActive"
          :label="$t('other.label')"
        />
      </v-col>
    </v-row>
    <v-row
      justify="center"
    >
      <v-col
        cols="6"
        sm="4"
        md="3"
      >
        <v-select
          v-model="form.subject"
          :items="subjects"
          item-text="label"
          item-value="id"
          :label="$t('subject.placeholder')"
        />
      </v-col>
      <v-col
        cols="6"
        sm="4"
        md="3"
      >
        <v-text-field
          v-model="form.otherSubject"
          :disabled="!isOtherSubjectActive"
          :label="$t('other.label')"
        />
      </v-col>
    </v-row>
    
    <!--user data-->
    
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
        >
          <!--<v-container>-->
          <v-row>
            <v-col
              cols="12"
            >
              <v-select
                v-model="form.gender"
                :items="genderItems"
                :rules="rules.genderRules"
                item-text="genderItem"
                item-value="genderValue"
                :label="$t('gender.placeholder') + ' *'"
              />
            </v-col>
            <v-col
              cols="12"
            >
              <v-text-field
                v-model="form.familyName"
                :label="$t('familyName.placeholder') + ' *'"
                :rules="rules.familyNameRules"
                name="lastName"
              />
            </v-col>
            <v-col
              cols="12"
            >
              <v-text-field
                v-model="form.givenName"
                :label="$t('givenName.placeholder') + ' *'"
                :rules="rules.givenNameRules"
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
                    v-model="yearOfBirth"
                    :label="$t('yearOfBirth.placeholder') + ' *'"
                    :rules="rules.yearsOfBirthRules"
                    v-on="on"
                  />
                </template>
                <v-date-picker
                  ref="picker"
                  v-model="form.yearOfBirth"
                  no-title
                  reactive
                  first-day-of-week="1"
                  :max="years.max"
                  :min="years.min"
                  @input="save"
                >
                  <v-spacer />
                  <v-btn
                    text
                    color="error"
                    @click="menu = false"
                  >
                    {{ $t('ui.buttons.cancel.label') }}
                  </v-btn>
                  <v-btn
                    text
                    color="secondary"
                    @click="$refs.menu.save(form.yearOfBirth)"
                  >
                    {{ $t('ui.buttons.validate.label') }}
                  </v-btn>
                </v-date-picker>
              </v-menu>
            </v-col>
            <v-col
              cols="12"
            >
              <v-text-field
                v-model="form.email"
                :label="$t('email.placeholder') + ' *'"
                :rules="rules.emailRules"
                name="email"
              />
            </v-col>
            <v-col
              cols="12"
            >
              <v-text-field
                v-model="form.phoneNumber"
                :label="$t('phone.placeholder') + ' *'"
                :rules="rules.phoneNumberRules"
                name="phone"
              />
            </v-col>
            <v-col
              cols="12"
            >
              <v-switch
                v-model="form.hasRSA"
                color="primary"
                inset
                :label="$t('hasRSA.placeholder')"
              />
            </v-col>
          </v-row>
            
          <!--submission-->
          <v-btn
            :disabled="!isValid"
            :loading="loading"
            color="secondary"
            rounded
            @click="validate"
          >
            {{ $t('ui.buttons.validate.label') }}
          </v-btn>
        </v-form>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import {find} from "lodash";
import axios from "axios";
import moment from "moment";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/solidary/SolidaryForm/";
import SearchJourney from "@components/carpool/search/SearchJourney";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  components: {
    SearchJourney
  },
  props: {
    geoSearchUrl: {
      type: String,
      default: ""
    },
    user: {
      type: Object,
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
    structures: {
      type: Array,
      default: null
    },
    subjects: {
      type: Array,
      default: null
    }
  },
  data () {
    return {
      locale: this.$i18n.locale,
      loading: false,
      valid: false,
      alert: {
        type: "success",
        show: false,
        message: ""
      },
      pickerActive: false,
      form: {
        structure: null,
        otherStructure: "",
        subject: null,
        otherSubject: "",
        gender: this.user && this.user.gender ? this.user.gender : null,
        givenName: this.user && this.user.givenName ? this.user.givenName : "",
        familyName: this.user && this.user.familyName ? this.user.familyName : "",
        email: this.user && this.user.email ? this.user.email : "",
        phoneNumber: this.user && this.user.telephone ? this.user.telephone : null,
        yearOfBirth: this.user && this.user.birthYear ? moment(this.user.birthYear.toString()).format("YYYY-MM-DD") : null,
        hasRSA: false,
        search: null
      },
      genderItems: [
        { genderItem: this.$t('gender.values.female'), genderValue: 1 },
        { genderItem: this.$t('gender.values.male'), genderValue: 2 },
        { genderItem: this.$t('gender.values.other'), genderValue: 3 },
      ],
      rules: {
        genderRules: [
          v => !!v || this.$t("gender.errors.required"),
        ],
        givenNameRules: [
          v => !!v || this.$t("givenName.errors.required"),
        ],
        familyNameRules: [
          v => !!v || this.$t("familyName.errors.required"),
        ],
        phoneNumberRules: [
          v => !!v || this.$t("phone.errors.required"),
          v => (/^((\+)33|0)[1-9](\d{2}){4}$/).test(v) || this.$t("phone.errors.valid")
        ],
        emailRules: [
          v => !!v || this.$t("email.errors.required"),
          v => /.+@.+/.test(v) || this.$t("email.errors.valid")
        ],
        yearsOfBirthRules: [
          v => !!v || this.$t("yearOfBirth.errors.required"),
        ],
      },
      years: {
        max: moment().format(),
        min: moment().subtract(100, 'years').format()
      }
    }
  },
  computed: {
    // we can't get only year from v-datepicker so we have to create custom getter and setter 
    // to handle what we want in case user wants to type for the year
    // no autocompletion from typing
    yearOfBirth: {
      get () {
        return this.form.yearOfBirth && moment(this.form.yearOfBirth, "YYYY-MM-DD", true).isValid() ? 
          moment(this.form.yearOfBirth).format('YYYY') : null
      },
      set (value) {
        value && moment(value, "YYYY", true).isValid() ?
          this.form.yearOfBirth = moment(value).format("YYYY-MM-DD") : null;
      }
    },
    isValid () {
      return this.valid && (this.form.search && this.form.search.origin && this.form.search.destination && this.form.search.date)
    },
    // todo: trouver une meilleure solution, modifier en utilisant un slug ?
    isOtherStructureActive() {
      return this.form.structure ? find(this.structures, {id: this.form.structure}).name === "Autre" : false;
    },
    isOtherSubjectActive() {
      return this.form.subject ? find(this.subjects, {id: this.form.subject}).label === "Autre" : false;
    }
  },
  watch: {
    pickerActive(val) {
      val && this.$nextTick(() => (this.$refs.picker.activePicker = 'YEAR'))
    },
  },
  created() {
    moment.locale(this.locale); // DEFINE DATE LANGUAGE
  },
  methods: {
    save (date) {
      this.$refs.menu.save(date);
      this.$refs.picker.activePicker = 'YEAR';
      this.menu = false;
    },
    searchChanged(data) {
      this.form.search = data
    },
    validate() {
      const self = this;
      this.resetAlert();
      if (this.$refs.form.validate()) {
        this.loading = true;
        axios.post(this.$t('ui.buttons.validate.route'), this.form)
          .then(function (response) {
            console.log(response.data);
            if (response.data && response.data.message) {
              self.alert = {
                type: "success",
                message: self.$t(response.data.message)
              };
            }
          })
          .catch(function (error) {
            console.error(error.response);
            let messages = "";
            if (error.response.data && error.response.data.errors) {
              error.response.data.errors.forEach(error => {
                messages += self.$t(error) + "<br>"
              });
            } else if (error.response.data && error.response.data.message) {
              messages = self.$t(error.response.data.message);
            }
            self.alert = {
              type: "error",
              message: messages
            };
          }).finally(function () {
            self.loading = false;
            if (self.alert.message.length > 0) {
              self.alert.show = true;
            }
          })
      }
    },
    resetAlert() {
      this.alert = {
        type: "success",
        message: "",
        show: false
      }
    }
  }
}
</script>

<style scoped>

</style>