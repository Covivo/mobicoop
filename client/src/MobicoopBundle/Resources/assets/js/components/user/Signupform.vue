<template>
  <section>
    <div class="tile is-ancestor">
      <div class="tile is-vertical is-12">
        <div class="tile is-child center-all">
          <form-wizard
            :back-button-text="$t('ui.button.previous')"
            :next-button-text="$t('ui.button.next')"
            :finish-button-text="$t('ui.button.register')"
            title=""
            subtitle=""
            color="#023D7F"
            class="tile is-vertical is-8"
            @on-complete="checkForm"
          >
            <v-alert
              v-if="errors.length"
              type="error"
              class="text-left"
            >
              <b>{{ $t('ui.form.errors') }}:</b>
              <ul>
                <li
                  v-for="error in errors"
                  :key="error.id"
                  class="is-danger"
                >
                  {{ error }}
                </li>
              </ul>
            </v-alert>
            <tab-content
              title=""
              icon=""
              class="tabContent"
            >
              <b-field
                :label="$t('models.user.email.label')"
              >
                <b-input
                  v-model="form.email"
                  type="email"
                  :placeholder="$t('models.user.email.placeholder')"
                  class="email"
                />
              </b-field>
              <b-field :label="$t('models.user.phone.label')">
                <b-input
                  v-model="form.telephone"
                  :placeholder="$t('models.user.phone.placeholder')"
                  class="telephone"
                />
              </b-field>
              <b-field :label="$t('models.user.password.label')">
                <b-input
                  v-model="form.password"
                  class="password"
                  type="password"
                  password-reveal
                  :placeholder="$t('models.user.password.placeholder')"
                />
              </b-field>
            </tab-content>

            <tab-content
              title=""
              icon=""
              class="tabContent"
            >
              <b-field :label="$t('models.user.givenName.label')">
                <b-input
                  v-model="form.givenName"
                  :placeholder="$t('models.user.givenName.placeholder')"
                  class="givenName"
                />
              </b-field>
              <b-field :label="$t('models.user.familyName.label')">
                <b-input
                  v-model="form.familyName"
                  :placeholder="$t('models.user.familyName.placeholder')"
                  class="familyName"
                />
              </b-field>
            </tab-content>

            <tab-content
              title=""
              icon=""
              class="tabContent"
            >
              <b-field :label="$t('models.user.gender.label')">
                <b-select
                  v-model="form.gender"
                  :placeholder="$t('models.user.gender.placeholder')"
                  class="gender"
                >
                  <option value="1">
                    {{ $t('models.user.gender.values.female') }}
                  </option>
                  <option value="2">
                    {{ $t('models.user.gender.values.male') }}
                  </option>
                  <option value="3">
                    {{ $t('models.user.gender.values.other') }}
                  </option>
                </b-select>
              </b-field>
            </tab-content>

            <tab-content
              title=""
              icon=""
              class="tabContent"
            >
              <b-field :label="$t('models.user.birthYear.label')">
                <b-select
                  v-model="form.birthYear"
                  :placeholder="$t('models.user.birthYear.placeholder')"
                  class="birthYear"
                >
                  <option
                    v-for="year in years"
                    :key="year.id"
                    :value="year"
                  >
                    {{ year }}
                  </option>
                </b-select>
              </b-field>
            </tab-content>

            <tab-content
              title=""
              icon=""
              class="tabContent"
            >
              <GeoComplete
                id="homeAddress"
                name="homeAddress"
                :placeholder="$t('models.user.homeTown.placeholder')"
                :url="geoSearchUrl"
                @geoSelected="selectedGeo"
              />
              <div class="field">
                <b-checkbox
                  v-model="form.validation"
                  class="check"
                >
                  {{ $t('ui.pages.signup.chart.chartValid') }}
                </b-checkbox>
              </div>
            </tab-content>
          </form-wizard>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/Login.json";
import TranslationsClient from "@clientTranslations/components/Login.json";
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
    GeoComplete
  },
  props: {
    geoSearchUrl: {
      type: String,
      default: ""
    },
    sentToken: {
      type: String,
      default: ""
    }
  },
  data() {
    return {
      errors: [],
      homeAddress:{
        required: true,
        value: {}
      },
      form:{
        createToken: this.sentToken,
        email: null,
        givenName: null,
        familyName: null,
        gender: null,
        birthYear: null,
        telephone: null,
        password: null,
        validation: false,
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
      const year = new Date().getFullYear()
      return Array.from({length: year - 1910}, (value, index) => 1910 + index)
    },
  },
  methods: {
    selectedGeo(val) {
      let name = val.name;
      this[name] = val;
      this.form.addressCountry = val.addressCountry
      this.form.addressLocality = val.addressLocality
      this.form.countryCode = val.countryCode
      this.form.county = val.county
      this.form.latitude = val.latitude
      this.form.localAdmin = val.localAdmin
      this.form.longitude = val.longitude
      this.form.macroCounty = val.macroCounty
      this.form.macroRegion = val.macroRegion
      this.form.name = val.name
      this.form.region = val.region
      this.form.street = val.street
      this.form.streetAddress = val.streetAddress
      this.form.subLocality = val.subLocality
      this.form.postalCode = val.postalCode
    },
    checkForm: function (e) {
      console.log("checking form");
      if (this.form.email && this.form.telephone && this.form.password && this.form.givenName && this.form.familyName && this.form.gender && this.form.birthYear && this.form.validation == true) {
        console.log("passed");
        let userForm = new FormData;
        for (let prop in this.form) {
          console.log(prop)
          let value = this.form[prop];
          console.log(value)
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
            // window.location.href = '/';
            console.log("is account created ?")
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
      if (this.form.validation == false) {
        this.errors.push(this.$t('ui.pages.signup.chart.errors.required'));
      }
      // e.preventDefault();
    },
  }
};
</script>