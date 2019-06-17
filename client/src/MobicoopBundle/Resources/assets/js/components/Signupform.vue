<template>
  <section>
    <form>
      <b-field
        label="Email"
      >
        <b-input
          v-model="email"
          type="email"
        />
      </b-field>
      <b-field label="PhoneNumber">
        <b-input v-model="telephone" />
      </b-field>
      <b-field label="Password">
        <b-input
          v-model="password"
          type="password"
          value="iwantmytreasure"
          password-reveal
        />
      </b-field> 
      
      <b-field label="GivenName">
        <b-input v-model="givenName" />
      </b-field>
      <b-field label="FamilyName">
        <b-input v-model="familyName" />
      </b-field>
     
      <b-field label="Civilité">
        <b-select
          v-model="gender"
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
          v-model="birthYear"
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
        <b-checkbox>Je valide la charte</b-checkbox>
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
      homeAddress: {},
      email: null,
      givenName: null,
      familyName: null,
      gender: null,
      birthYear: null,
      telephone: null,
      password: null,
      token: null,
      
    };
  },
  computed : {
    years () {
      const year = new Date().getFullYear()
      return Array.from({length: year - 1910}, (value, index) => 1910 + index)
    }
  },
  methods: {
    selectedGeo(val) {
      let name = val.name;
      this[name] = val;
    },
    onComplete() { 
      let signupForm = new FormData();
      for (let prop in this.form) {
        let value = this.form[prop];
        if(!value) continue; // Value is empty, just skip it!
        // Convert date to required format
       
        // rename prop to be usable in the controller
        let renamedProp = prop === "createToken" ? prop : `signup_Form[${prop}]`;
        signupForm.append(renamedProp, value);
      }
      //  We post the form 🚀
      axios
        .post("/utilisateur/inscription", signupForm, {
          headers: {
            "Content-Type": "multipart/form-data"
          }
        })
        
        // .then(function(response) {
        //   window.location.href = '/';
        //   //console.log(response.data.proposal);
        // })
        .catch(function(error) {
          console.error(error);
        });
    }
  }
  
};
</script>
