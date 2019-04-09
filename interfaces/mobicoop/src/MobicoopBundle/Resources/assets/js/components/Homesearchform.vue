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
            />
          </div>
          <div class="column">
            <geocomplete
              placeholder="Vers"
              :url="geoSearchUrl"
              name="destination"
              @geoSelected="selectedGeo"
            />
          </div>
          <div class="column">
            <b-datepicker
              v-model="outwardDate"
              :placeholder="'Date de départ...'"
              :day-names="daysShort"
              :month-names="months"
              :first-day-of-week="1"
              class="column is-full"
              position="is-top-right"
              icon-pack="fas"
            />
          </div>
          <div class="column">
            <button @click="onClick">
              rechercher
            </button>
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
    baseUrl: {
      type: String,
      default: ""
    }
  },
  data() {
    return {
      daysShort: [
        "Dim",
        "Lun",
        "Mar",
        "Mer",
        "Jeu",
        "Ven",
        "Sam"
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
      originLatitude: null,
      originLongitude: null,
      destinationLatitude: null,
      destinationLongitude: null,
      outwardDate: null,
    };
  },
  computed: {
    urlToCall() {
      return `${this.baseUrl}/${this.originLatitude}/${this.originLongitude}/${this.destinationLatitude}/${this.destinationLongitude}/resultats`
    } 
  },

  methods: {
    selectedGeo(val) {
      let name = val.name;
      this[name + "Latitude"] = val.latitude;
      this[name + "Longitude"] = val.longitude;
    },

    /**
     * Send the search to the route /covoiturage/recherche/origin_lat/origin_lon/destination_lat/destination_lon/yyyymmddhhiiss/resultats
     */
    onClick() { 
      //  We send the seach 🚀
      axios
        .get(`${this.urlToCall}`, {
        })
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