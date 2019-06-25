<template>
  <section>
    <div class="tile is-ancestor">
      <div class="tile is-vertical is-12">
        <div class="tile is-child center-all">
          <p v-if="errors.length">
            <b>Please correct the following error(s):</b>
            <ul>
              <li
                v-for="error in errors"
                :key="error.id"
                class="is-danger"
              >
                {{ error }}
              </li>
            </ul>
          </p>
          <form>
            <b-field
              label="Email"
            >
              <b-input
                v-model="form.email"
                type="email"
                placeholder="Email"
              />
            </b-field>
            <b-field label="PhoneNumber">
              <b-input 
                v-model="form.telephone"
                placeholder="Numéro de téléphone"
              />
            </b-field>
            <b-field label="GivenName">
              <b-input
                v-model="form.givenName" 
                placeholder="Prénom"
              />
            </b-field>
            <b-field label="FamilyName">
              <b-input
                v-model="form.familyName" 
                placeholder="Nom"
              />
            </b-field>
           
            <b-field label="Civilité">
              <b-select
                v-model="form.gender"
                placeholder="Civilité"
              >
                <option value="1">
                  Madame
                </option>
                <option value="2">
                  Monsieur
                </option>
                <option value="3">
                  Autre
                </option>
              </b-select>
            </b-field>
          
            <b-field label="Année de naissance">
              <b-select
                v-model="form.birthYear"
                placeholder="Année de naissance"
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
           
            <geocomplete
              id="homeAddress"
              name="homeAddress"
              :placeholder="addressLocality"
              :url="geoSearchUrl"
              @geoSelected="selectedGeo"
            />
            <button @click="checkForm">
              Enregistrer
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>                
</template>

<script>
import axios from "axios";
import Geocomplete from "./Geocomplete";
// import BDatepicker from "buefy/src/components/datepicker/Datepicker";
export default {
  name: 'Resultssearchform',
  components: {
    // BDatepicker,
    Geocomplete
  },
  props: {
    geoSearchUrl: {
      type: String,
      default: ""
    },
    sentToken: {
      type: String,
      default: ""
    },
    givenName: {
      type: String,
      default: ""
    },
    familyName: {
      type: String,
      default: ""
    },
    gender: {
      type: String,
      default: ""
    },
    birthYear: {
      type: String,
      default: ""
    },
    telephone: {
      type: String,
      default: ""
    },
    email: {
      type: String,
      default: ""
    },
    addressCountry: {
      type: String,
      default: ""
    },
    addressLocality: {
      type: String,
      default: ""
    },
    countryCode: {
      type: String,
      default: ""
    },
    county: {
      type: String,
      default: ""
    },
    latitude: {
      type: String,
      default: ""
    },
    localAdmin: {
      type: String,
      default: ""
    },
    longitude: {
      type: String,
      default: ""
    },
    macroCounty: {
      type: String,
      default: ""
    },
    macroRegion: {
      type: String,
      default: ""
    },
    postalCode: {
      type: String,
      default: ""
    },
    region: {
      type: String,
      default: ""
    },
    street: {
      type: String,
      default: ""
    },
    streetAddress: {
      type: String,
      default: ""
    },
    subLocality: {
      type: String,
      default: ""
    },
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
        name: null,
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
    checkForm: function (e) {
      if (this.form.email && this.form.telephone && this.form.password && this.form.givenName && this.form.familyName && this.form.gender && this.form.birthYear && this.form.validation == true) {
        let userForm = new FormData;
        for (let prop in this.form) {
          let value = this.form[prop];
          // if(!value) continue;
          // let renamedProp = `user_form[${prop}]`;
          // userForm.append(renamedProp, value);
          let renamedProp = prop === "createToken" ? prop : `user_form[${prop}]`;
          userForm.append(renamedProp, value);
        }
        axios 
          .post("/utilisateur/profil/modifier", userForm, {
            headers: {
              "Content-Type": "multipart/form-data"
            }
          } )
          // .then(function(response) {
          //   window.location.href = '/';
          //   console.error(response);
          // })
          // .catch(function(error) {
          //   console.error(error);
          // });  
      } 
    },
  }
};
</script>
