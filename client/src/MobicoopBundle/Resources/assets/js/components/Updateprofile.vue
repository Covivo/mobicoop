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
          <form
            id="app"
            method="post"
          >
            <div class="columns">
              <div class="column">
                <b-field
                  :label="$t('models.user.email.label')"
                >
                  <b-input
                    v-model="form.email"
                    type="email"
                    :placeholder="$t('models.user.email.placeholder')"
                  />
                </b-field>
              </div>
              <div class="column">
                <b-field :label="$t('models.user.phone.label')">
                  <b-input 
                    v-model="form.telephone"
                    :placeholder="$t('models.user.phone.placeholder')"
                  />
                </b-field>
              </div>
            </div> 
            <div class="columns">
              <div class="column">  
                <b-field :label="$t('models.user.givenName.label')">
                  <b-input
                    v-model="form.givenName" 
                    :placeholder="$t('models.user.givenName.placeholder')"
                  />
                </b-field>
              </div>
              <div class="column">
                <b-field :label="$t('models.user.familyName.label')">
                  <b-input
                    v-model="form.familyName" 
                    :placeholder="$t('models.user.familyName.placeholder')"
                  />
                </b-field>
              </div>
              <div class="column">
                <b-field :label="$t('models.user.gender.label')">
                  <b-select
                    v-model="form.gender"
                    :placeholder="$t('models.user.gender.placeholder')"
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
              </div>
            </div>  

            <div class="columns">
              <div class="column">  
                <b-field :label="$t('models.user.birthYear.label')">
                  <b-select
                    v-model="form.birthYear"
                    :placeholder="$t('models.user.birthYear.placeholder')"
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
              </div>
              <div class="column">
                <b-field :label="$t('models.user.homeTown.label')">
                  <geocomplete
                    id="homeAddress"
                    name="homeAddress"
                    :placeholder="addressLocality != 'null' ? addressLocality : home"
                    :url="geoSearchUrl"
                    @geoSelected="selectedGeo"
                  />
                </b-field>
              </div>
            </div>  
            <div class="columns">
              <div class="column">  
                <input
                  type="submit"
                  :value="$t('ui.button.save')"
                  @click="checkForm"
                >
              </div>
            </div>
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
        birthYear: parseInt(this.birthYear, 10),
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
    },
  }
};
</script>
