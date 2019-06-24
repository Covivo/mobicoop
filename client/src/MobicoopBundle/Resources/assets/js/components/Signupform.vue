<template>
  <section>
    <div class="tile is-ancestor">
      <div class="tile is-vertical is-12">
        <div class="tile is-child center-all">
          <form-wizard
            back-button-text="Précédent"
            next-button-text="Suivant"
            finish-button-text="Je m'inscris"
            title=""
            subtitle=""
            color="#023D7F"
            class="tile is-vertical is-8"
            @on-complete="checkForm"
          >
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
            <tab-content
              title=""
              icon=""
              class="tabContent"
            >
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
              <b-field label="Password">
                <b-input
                  v-model="form.password"
                  type="password"
                  password-reveal
                  placeholder="Mot de passe"
                />
              </b-field> 
            </tab-content>

            <tab-content
              title=""
              icon=""
              class="tabContent"
            >
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
            </tab-content>

            <tab-content
              title=""
              icon=""
              class="tabContent"
            >     
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
            </tab-content>

            <tab-content
              title=""
              icon=""
              class="tabContent"
            >
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
            </tab-content>

            <tab-content
              title=""
              icon=""
              class="tabContent"
            >
              <geocomplete
                id="homeAddress"
                name="homeAddress"
                placeholder="Commune de résidence"
                :url="geoSearchUrl"
                @geoSelected="selectedGeo"
              />

              <div class="field">
                <b-checkbox
                  v-model="form.validation"
                >
                  Je valide la charte
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
          .post("/utilisateur/inscription", userForm, {
            headers: {
              "Content-Type": "multipart/form-data"
            }
          } )
          .then(function(response) {
            window.location.href = '/';
            console.error(response);
          })
          .catch(function(error) {
            console.error(error);
          });  
      } 
      this.errors = [];

      if (!this.form.email) {
        this.errors.push('Email required.');
      } 
      if (!this.form.telephone) {
        this.errors.push('Telephone required.');
      }
      if (!this.form.password) {
        this.errors.push('Password required.');
      }
      if (!this.form.givenName) {
        this.errors.push('GivenName required.');
      }
      if (!this.form.familyName) {
        this.errors.push('FamilyName required.');
      }
      if (!this.form.gender) {
        this.errors.push('Gender required.');
      }
      if (!this.form.birthYear) {
        this.errors.push('BirthYear required.');
      }
      if (this.form.validation == false) {
        this.errors.push('Validation required.');
      }
      e.preventDefault();
    },
  }
};
</script>
