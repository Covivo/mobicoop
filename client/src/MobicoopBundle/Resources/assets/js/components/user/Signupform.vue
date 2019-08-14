<template>
  <v-content>
    <v-container
      id="scroll-target"
      style="max-height: 500px"
      class="overflow-y-auto"
      fluid
    >
      <v-row
        justify="center"
        align="center"
      >
        <v-col
          cols="2"
          align="center"
        >
          <v-form
            id="scrolled-content"
            ref="form"
            v-model="valid"
          >
            <!--STEP 1-->
            <v-form
              ref="step 1"
              v-model="step1"
            >
              <v-text-field
                id="email"
                v-model="form.email"
                :rules="form.emailRules"
                :label="$t('models.user.email.placeholder')+` *`"
                name="email"
                required
              />
              <v-text-field
                v-model="form.telephone"
                :label="$t('models.user.phone.placeholder')"
                name="telephone"
                @keypress="isNumber(event)"
              />
              <v-text-field
                v-model="form.password"
                :append-icon="form.showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                :rules="form.passwordRules"
                :type="form.showPassword ? 'text' : 'password'"
                name="password"
                :label="$t('models.user.password.placeholder')+` *`"
                required
                @click:append="form.showPassword = !form.showPassword"
              />
              <v-btn
                ref="button"
                v-model="form.email"
                class="my-13"
                color="primary"
                :disabled="!step1"
                @click="$vuetify.goTo('#givenName', options)"
              >
                {{ $t('ui.button.next') }}
              </v-btn>
            </v-form>

            <!--STEP 2-->
            <v-form
              ref="step 2"
              v-model="step2"
            >
              <v-text-field
                id="givenName"
                v-model="form.givenName"
                :rules="form.givenNameRules"
                :label="$t('models.user.givenName.placeholder')+` *`"
                class="givenName"
                required
              />
              <v-text-field
                v-model="form.familyName"
                :rules="form.familyNameRules"
                :label="$t('models.user.familyName.placeholder')+` *`"
                class="familyName"
                required
              />
              <v-btn
                ref="button"
                class="my-13"
                color="primary"
                :disabled="!step2"
                @click="$vuetify.goTo('#gender', options)"
              >
                {{ $t('ui.button.next') }}
              </v-btn>
            </v-form>
            <!--STEP 3-->
            <v-form
              ref="step 3"
              v-model="step3"
            >
              <v-select
                id="gender"
                v-model="form.gender"
                :items="form.genderItems"
                item-text="genderItem"
                item-value="genderValue"
                :rules="form.genderRules"
                :label="$t('models.user.gender.placeholder')+` *`"
                class="gender"
                required
              />
              <v-btn
                ref="button"
                class="my-13"
                color="primary"
                :disabled="!step3"
                @click="$vuetify.goTo('#birthYear', options)"
              >
                {{ $t('ui.button.next') }}
              </v-btn>
            </v-form>

            <!--STEP 4-->
            <v-form
              ref="step 4"
              v-model="step4"
            >
              <v-select
                id="birthYear"
                v-model="form.birthYear"
                :items="years"
                :rules="form.birthYearRules"
                :label="$t('models.user.birthYear.placeholder')+` *`"
                class="birthYear"
                required
              >
                <option
                  v-for="year in years"
                  :key="year.id"
                  :value="year"
                >
                  <!--@TODO: convert option to vuetify -> no data available-->
                  {{ year }}
                </option>
              </v-select>
              <v-btn
                ref="button"
                class="my-13"
                color="primary"
                :disabled="!step4"
                @click="$vuetify.goTo('#homeAddress', options)"
              >
                {{ $t('ui.button.next') }}
              </v-btn>
            </v-form>

            <!--STEP 5-->
            <GeoComplete
              id="homeAddress"
              name="homeAddress"
              :label="$t('models.user.homeTown.placeholder')"
              :url="geoSearchUrl"
              @geoSelected="selectedGeo"
            />
            <v-checkbox
              v-model="form.validation"
              class="check"
              color="primary"
              :rules="form.checkbox"
              :label="$t('ui.pages.signup.chart.chartValid')"
              required
            />
            <v-btn
              color="primary"
              class="mr-4 mb-100 mt-12"
              :disabled="!valid"
              @click="validate"
            >
              {{ $t('ui.button.register') }}
            </v-btn>
          </v-form>
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>

<script>
import axios from "axios";
import GeoComplete from "@js/components/GeoComplete";
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/SignUp.json";
import TranslationsClient from "@clientTranslations/components/SignUp.json";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  components: {
    GeoComplete,
  },
  //@TODO: Uncomment when geocomplete field is up
  props: {
    geoSearchUrl: {
      type: String,
      default: null
    },
    sentToken: {
      type: String,
      default: null
    },
    ageMin: {
      type: String,
      default: null
    },
    ageMax: {
      type: String,
      default: null
    }
  },

  data() {
    return {
      event: null,
      errors: [],
      valid: true,

      //step validators
      step1: true,
      step2: true,
      step3: true,
      step4: true,

      //scrolling data
      type: 'selector',
      selected: null,
      duration: 1000,
      //offset : avoid being hidden by header
      offset: 150,
      easing: "easeOutQuad",
      container: "scroll-target",

      homeAddress:{
        required: true,
        value: {}
      },
      form:{
        createToken: this.sentToken,
        email: null,
        emailRules: [
          v => !!v || this.$t("models.user.email.errors.required"),
          v => /.+@.+/.test(v) || this.$t("models.user.email.errors.valid")
        ],
        givenName: null,
        givenNameRules: [
          v => !!v || this.$t("models.user.givenName.errors.required"),
        ],
        familyName: null,
        familyNameRules: [
          v => !!v || this.$t("models.user.familyName.errors.required"),
        ],
        gender: null,
        genderRules: [
          v => !!v || this.$t("models.user.gender.errors.required"),
        ],
        genderItems: [
          { genderItem: this.$t('models.user.gender.values.female'), genderValue: '1' },
          { genderItem: this.$t('models.user.gender.values.male'), genderValue: '2' },
          { genderItem: this.$t('models.user.gender.values.other'),genderValue: '3' },
        ],
        birthYear: null,
        birthYearRules: [
          v => !!v || this.$t("models.user.birthYear.errors.required"),
        ],
        telephone: null,
        password: null,
        showPassword: false,
        passwordRules: [
          v => !!v || this.$t("models.user.password.errors.required")
        ],
        checkbox: [
          v => !!v || 'You must agree to continue!'
        ],
        validation: null,//TODO : remove when finished
        addressCountry: null,
        addressLocality: null,
        countryCode: null,
        county: null,
        latitude: null,
        localAdmin: null,
        longitude: null,
        macroCounty: null,
        macroRegion: null,
        name: null,
        postalCode: null,
        region: null,
        street: null,
        streetAddress: null,
        subLocality: null
      }
    };
  },
  computed : {
    years () {
      const currentYear = new Date().getFullYear();
      const ageMin = Number(this.ageMin);
      const ageMax = Number(this.ageMax);
      return Array.from({length: ageMax - ageMin}, (value, index) => (currentYear - ageMin) - index)
    },
    //options of v-scroll
    options () {
      return {
        duration: this.duration,
        offset: this.offset,
        easing: this.easing,
        container: this.container,
      }
    }
  },
  mounted: function () {
    // this.elem = document.getElementById ( "scrolled-content" )//TODO: REMOVE
    this.container = document.getElementById ( "scroll-target" )
    // this.container.scrollTop = Math.floor ( this.elem.offsetHeight )
  },
  methods: {
    selectedGeo(val) {
      let name = val.name;
      this[name] = val;
      this.form.addressCountry = val.addressCountry;
      this.form.addressLocality = val.addressLocality;
      this.form.countryCode = val.countryCode;
      this.form.county = val.county;
      this.form.latitude = val.latitude;
      this.form.localAdmin = val.localAdmin;
      this.form.longitude = val.longitude;
      this.form.macroCounty = val.macroCounty;
      this.form.macroRegion = val.macroRegion;
      this.form.name = val.name;
      this.form.region = val.region;
      this.form.street = val.street;
      this.form.streetAddress = val.streetAddress;
      this.form.subLocality = val.subLocality;
      this.form.postalCode = val.postalCode
    },

    checkForm: function (e) {
      console.log("checking form");
      if (this.form.email && this.form.telephone && this.form.password && this.form.givenName && this.form.familyName && this.form.gender && this.form.birthYear && this.form.validation === true) {
        console.log("passed");
        let userForm = new FormData;
        for (let prop in this.form) {
          console.log(prop);
          let value = this.form[prop];
          console.log(value);
          // if(!value) continue;
          // let renamedProp = `user_form[${prop}]`;
          let renamedProp = prop === "createToken" ? prop : `user_form[${prop}]`;
          userForm.append(renamedProp, value);
          // userForm.set(prop, value);
          // userForm.set(renamedProp, value);
        }
        axios
          .post("/utilisateur/inscription", userForm, {
            headers: {
              "Content-Type": "multipart/form-data"
            }
          })
          .then(function(response) {
            // window.location.href = '/'; //@TODO : decommenter pour rediriger
            console.log("is account created ?");
            console.error(response);
          })
          .catch(function(error) {
            console.error(error);
          });
      }
      this.errors = [];

      if (!this.form.email) {
        this.errors.push(this.$t('models.user.email.errors.required'));
      }
      if (!this.form.telephone) {
        this.errors.push(this.$t('models.user.phone.errors.required'));
      }
      if (!this.form.password) {
        this.errors.push(this.$t('models.user.password.errors.required'));
      }
      if (!this.form.givenName) {
        this.errors.push(this.$t('models.user.givenName.errors.required'));
      }
      if (!this.form.familyName) {
        this.errors.push(this.$t('models.user.familyName.errors.required'));
      }
      if (!this.form.gender) {
        this.errors.push(this.$t('models.user.gender.errors.required'));
      }
      if (!this.form.birthYear) {
        this.errors.push(this.$t('models.user.birthYear.errors.required'));
      }
      // if (!this.form.longitude) {
      //   this.errors.push(this.$t('models.user.homeTown.errors.required'));
      // }//TODO : a retirer et mettre un texte (position pas obligatoire à fournir [..])
      if (this.form.validation === false) {
        this.errors.push(this.$t('ui.pages.signup.chart.errors.required'));
      }
      e.preventDefault();
    },

    validate: function (e) {
      let userForm = new FormData;
      for (let prop in this.form) {
        console.log(prop);
        let value = this.form[prop];
        console.log(value);
        let renamedProp = prop === "createToken" ? prop : `user_form[${prop}]`;
        userForm.append(renamedProp, value);
        // userForm.set(prop, value);
        // userForm.set(renamedProp, value);
      }
      axios
        .post("/utilisateur/inscription", userForm, {
          headers: {
            "Content-Type": "multipart/form-data"
          }
        })
        .then(function(response) {
          // window.location.href = '/'; //@TODO : decommenter pour rediriger
          console.log("is account created ?");
          console.error(response);
        })
        .catch(function(error) {
          console.error(error);
        });
    },

    isNumber: function(evt) {
      evt = (evt) ? evt : window.event;
      var charCode = (evt.which) ? evt.which : evt.keyCode;
      if (-(charCode < 48 || charCode > 57) && charCode !== 46) {
        evt.preventDefault();;
      } else {
        return true;
      }
    },

  }
};
</script>

<style>
  /*html,body,v-container{*/
  /*  height: 100%;*/
  /*  overflow-y:hidden;*/
  /*  scrollbar-width: none;*/
  /*  scrollbar-color: transparent transparent;*/
  /*  overflow: -moz-scrollbars-none;*/
  /*}*/


  @-moz-document url-prefix() { /* Disable scrollbar Firefox */
  html,body,v-container{
    scrollbar-width: none;
    scrollbar-color: transparent transparent;
  }
  }

  html { /* Disable scrollbar Chrome & Chromium */
    overflow: scroll;
    overflow-x: hidden;
  }
  ::-webkit-scrollbar {
    width: 0px;  /* Remove scrollbar space */
    background: transparent;  /* Optional: just make scrollbar invisible */
  }




  /*hide scrollbar for chrome / firefox*/
  /*.element::-webkit-scrollbar { width: 0 !important }*/
  /*.element { overflow: -moz-scrollbars-none; }*/
  .my-13 {
    margin-bottom:  52px;
    margin-top:     52px;
  }

  .mb-100 {
    margin-bottom:  300px;
  }
  /*$(window).on("mousewheel", function(e){*/
  /*  e.preventDefault();*/
  /*});*/
/*  TODO : see disable user scroll*/
</style>