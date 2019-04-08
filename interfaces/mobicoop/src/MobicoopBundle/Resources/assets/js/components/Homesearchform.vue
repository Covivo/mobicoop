<template>
  <section class="section">
    <div class="tile is-ancestor">
      <div class="tile is-vertical is-12">
        <div class="tile is-child center-all">
          <div class="column">
            <geocomplete
              name="origin"
              placeholder="Depuis"
              :url="geoSearchUrl"
              @geoSelected="selectedGeo"
            ></geocomplete>
          </div>
          <div class="column">
            <geocomplete
              placeholder="Vers"
              :url="geoSearchUrl"
              name="destination"
              @geoSelected="selectedGeo"
            ></geocomplete>
          </div>
          <div class="column">
            <b-datepicker
                :placeholder="'Date de départ...'"
                v-model="form.outwardDate"
                :day-names="daysShort"
                :month-names="months"
                :first-day-of-week="1"
                class="column is-full"
                position="is-top-right"
                icon-pack="fas"
            ></b-datepicker>
          </div>
          <div class="column">
            <button v-on:click="recherche">Chercher</button>
          </div>  
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import axios from "axios";
import moment from "moment";
import Geocomplete from "./Geocomplete";
import _default from 'flatpickr/dist/l10n/fr';
export default {
  components: {
    Geocomplete
  },
  props: {
    geoSearchUrl: {
      type: String,
      default: ""
    },
    urlToCall: {
      type: String,
      default: ""
    }
  },
  data() {
    return {
      origin: null,
      outward: this.sentOutward,
      days: [
        "dimanche",
        "lundi",
        "mardi",
        "mercredi",
        "jeudi",
        "vendredi",
        "samedi"
      ],
      months: [
        "Janvier",
        "Fevrier",
        "Mars",
        "Avril",
        "Mai",
        "Juin",
        "Juillet",
        "Aout",
        "Septembre",
        "Octobre",
        "Novembre",
        "Décembre"
      ],
      form: {
        originLatitude: null,
        originLongitude: null,
        destinationLatitude: null,
        destinationLongitude: null,
        outwardDate: null,

      }
    };
  },
  computed: {
    daysShort() {
      return this.days.map(day => day.substring(0, 2));
    },
    nbOfDaysToPlan(){
      if(this.form.frequency === 2) return this.days.length;
      return 1;
    }
  },
  methods: {
    selectedGeo(val) {
      let name = val.name;
      this.form[name] = `${val.streetAddress ? val.streetAddress + " " : ""}${
        val.addressLocality
      } ${val.addressCountry}`;
      this.form[name + "Latitude"] = val.latitude;
      this.form[name + "Longitude"] = val.longitude;

      this.form[name + "StreetAddress"] = val.streetAddress;
      this.form[name + "PostalCode"] = val.postalCode;

      this.form[name + "AddressCountry"] = val.addressCountry;
      this.form[name + "AddressLocality"] = val.addressLocality;
    },
    /**
     * Send the form to the route ???
     */
    onComplete() { 
      let homeSearchForm = new FormData();
      for (let prop in this.form) {
        let value = this.form[prop];
        if(!value) continue; // Value is empty, just skip it!
        // Convert date to required format
        if(prop.toLowerCase().includes('date')){
          value = moment(value).format('YYYY/MM/DD');
        }
        // Convert time to required format
        if(prop.toLowerCase().includes('time')){
          value = moment(value).format('HH:mm');
        }
        // Convert margin from min to sec
        if(prop.toLowerCase().includes('margin')){
          value *= 60;
        }
        // rename prop to be usable in the controller
        let renamedProp = prop === "createToken" ? prop : `ad_form[${prop}]`;
        homeSearchForm.append(renamedProp, value);
      }
      //  We post the form 🚀
      axios
        .post("/covoiturage/annonce/poster")
        .then(function(response) {
          console.log(response);
        })
        .catch(function(error) {
          console.error(error);
        });
    }
  }
};
</script>

<style lang="scss" scoped>
.tabContent {
  text-align: center;
}

.fieldsContainer {
  display: flex;
  justify-content: center;
  align-items: center;
}

.dayNameColumn{
  text-align: left;
  a{
    width: 100%;
  }
}
</style>