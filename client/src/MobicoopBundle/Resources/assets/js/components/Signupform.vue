<template>
  <section>
    <form
      id="app"
      action="/utilisateur/inscription"
      method="post"
      @submit="checkForm"
    >
      <p v-if="errors.length">
        <b>Please correct the following error(s):</b>
        <ul>
          <li
            v-for="error in errors"
            :key="error.id"
          >
            {{ error }}
          </li>
        </ul>
      </p>
      <b-field
        label="Email"
      >
        <b-input
          v-model="form.email"
          type="email"
        />
      </b-field>
      <b-field label="PhoneNumber">
        <b-input v-model="form.telephone" />
      </b-field>
      <b-field label="Password">
        <b-input
          v-model="form.password"
          type="password"
          password-reveal
        />
      </b-field> 
      
      <b-field label="GivenName">
        <b-input v-model="form.givenName" />
      </b-field>
      <b-field label="FamilyName">
        <b-input v-model="form.familyName" />
      </b-field>
     
      <b-field label="Civilité">
        <b-select
          v-model="form.gender"
          placeholder="Civilité"
        >
          <option value="madame">
            Madame
          </option>
          <option value="monsieur">
            Monsieur
          </option>
          <option value="autre">
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
      <input
        type="submit"
        value="Je m'inscris"
      >
    </form>
  </section>                
</template>

<script>
import axios from "axios";
import moment from 'moment'
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
  },
  data() {
    return {
      errors: [],
      homeAddress:{
        required: true,
        value: {}
      },
      form:{
        email: null,
        givenName: null,
        familyName: null,
        gender: null,
        birthYear: null,
        telephone: null,
        password: null,
        validation: false
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
    },
    checkForm: function (e) {
      if (this.email && this.telephone && this.password && this.givenName && this.familyName && this.gender && this.birthYear && this.homeAddress && this.validation == true) {
        let signupForm = new FormData;
        for (let prop in this.form) {
          let value = this.form[prop];
          if(!value) continue;}
        console.error(signupForm)
        axios 
          .post("/utilisateur/inscription", signupForm, {
            headers: {
              "Content-Type": "multipart/form-data"
            }
          })
      }
      this.errors = [];

      if (!this.email) {
        this.errors.push('Email required.');
      } 
      if (!this.telephone) {
        this.errors.push('Telephone required.');
      }
      if (!this.password) {
        this.errors.push('Password required.');
      }
      if (!this.givenName) {
        this.errors.push('GivenName required.');
      }
      if (!this.familyName) {
        this.errors.push('FamilyName required.');
      }
      if (!this. gender) {
        this.errors.push('Gender required.');
      }
      if (!this.birthYear) {
        this.errors.push('BirthYear required.');
      }
      if (this.validation == false) {
        this.errors.push('Validation required.');
      }
      e.preventDefault();
    },
  }
};
</script>
