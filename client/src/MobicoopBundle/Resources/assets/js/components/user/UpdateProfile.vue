<template>
  <v-content>
    <v-container fluid>
      <v-layout
        justify-center
        text-center
      >
        <v-flex xs10>
          <v-form
            ref="form"
            v-model="valid"
            lazy-validation
          >
            <v-text-field
              v-model="user.email"
              :label="$t('models.user.email.label')"
              type="email"
              class="email"
            />

            <v-text-field
              v-model="user.telephone"
              :label="$t('models.user.phone.label')"
              class="telephone"
            />

            <v-text-field
              v-model="user.givenName"
              :label="$t('models.user.givenName.label')" 
              class="givenName"
            />

            <v-text-field
              v-model="user.familyName"
              :label="$t('models.user.familyName.label')" 
              class="familyName"
            />
          

            <v-btn
              class="button saveButton"
              type="button"
              :value="$t('ui.button.save')"
              @click="checkForm"
            >
              {{ $t('ui.button.save') }}
            </v-btn>
          </v-form>
        </v-flex>
      </v-layout>
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
    GeoComplete
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
    sentToken: {
      type: String,
      default: ""
    }
  },
  data() {
    return {
      valid: true,
      errors: [],
      loading: false,
      home: this.$t('models.user.homeTown.placeholder'),
      homeAddress:{
        required: true,
        value: {
          addressCountry: this.addressCountry,
          addressLocality: this.addressLocality,
          countryCode: this.countryCode,
          county: this.county,
          latitude: this.latitude,
          localAdmin: this.localAdmin,
          longitude: this.longitude,
          macroCounty: this.macroCounty,
          macroRegion: this.macroRegion,
          postalCode: this.postalCode,
          region: this.region,
          street: this.street,
          streetAddress: this.streetAddress,
          subLocality: this.subLocality,
        }
      },
      form:{
        createToken: this.sentToken,
        email: this.email,
        givenName: this.givenName,
        familyName: this.familyName,
        gender: this.gender,
        birthYear: this.birthYear,
        telephone: this.telephone,
        password: null,
        validation: false,
        addressCountry: this.addressCountry,
        addressLocality: this.addressLocality,
        countryCode: this.countryCode,
        county: this.county,
        latitude: this.latitude,
        localAdmin: this.localAdmin,
        longitude: this.longitude,
        macroCounty: this.macroCounty,
        macroRegion: this.macroRegion,
        postalCode: this.postalCode,
        region: this.region,
        street: this.street,
        streetAddress: this.streetAddress,
        subLocality: this.subLocality
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

    validate () {
      if (this.$refs.form.validate()) {
        this.checkForm();
      }
    },

    checkForm: function (e) {
      let userForm = new FormData;
      for (let prop in this.form) {
        let value = this.form[prop];
        // if(!value) continue;
        let renamedProp = `user_form[${prop}]`;
        userForm.append(renamedProp, value);
        //let renamedProp = prop === "createToken" ? prop : `user_form[${prop}]`;
        //userForm.append(renamedProp, value);
      }
      console.error(userForm);
      axios 
        .post("/utilisateur/profil/modifier", userForm, {
          headers: {
            "Content-Type": "multipart/form-data"
          }
        } )
      this.$toast.open({
        message: 'Votre profil a bien été mis à jour!',
        type: 'is-success',
      })  
    },
  }
};
</script>

