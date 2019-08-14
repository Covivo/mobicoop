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

            <v-select
              v-model="gender"
              :label="$t('models.user.gender.label')"
              :items="genders"
              item-text="gender"
              item-value="value"
            />

            <v-menu
              ref="menu"
              v-model="menu"
              :close-on-content-click="false"
              transition="scale-transition"
              offset-y
              full-width
              min-width="290px"
            >
              <template v-slot:activator="{ on }">
                <v-text-field
                  v-model="form.birthYear"
                  :label="$t('models.user.birthYear.label')"
                  v-on="on"
                />
              </template>
              <v-date-picker
                ref="picker"
                v-model="form.birthYear"
                :max="new Date().toISOString().substr(0, 10)"
                min="1950-01-01"
                @change="save"
              />
            </v-menu>

            <GeoComplete
              v-model="form.addressLocality"
              :url="geoSearchUrl"
              :label="$t('models.user.homeTown.label')"
              :token="user ? user.geoToken : ''"
              :home-address="user.homeAddress"
              @address-selected="homeAddressSelected"
            />

            <v-btn
              class="button saveButton"
              color="success"
              rounded
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
      default: null
    },
    user: {
      type: Object,
      default: null
    },
  },
  data() {
    return {
      valid: true,
      errors: [],
      loading: false,
      gender: {
        value: this.user.gender
      },

      genders:[
        { value: 2, gender: this.$t('models.user.gender.values.male')},
        { value: 1, gender: this.$t('models.user.gender.values.female')},
        { value: 3, gender: this.$t('models.user.gender.values.other')},
      ],
      home: this.$t('models.user.homeTown.placeholder'),
      homeAddress: null,
      menu: false,
      date:  null,
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
  watch: {
    menu (val) {
      val && setTimeout(() => (this.$refs.picker.activePicker = 'YEAR'))
    },
  },
  methods: {
    homeAddressSelected(address){
      this.homeAddress = address;
    },
    save (date) {
      this.$refs.menu.save(date)
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

