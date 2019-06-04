<template>
  <div class="tile center-all">
    <div
      class="columns is-centered is-vcentered SearchBar"
    >
      <div class="column has-text-centered is-one-third">
        <!-- inputs outward destination -->
        <label
          class="label"
          for="origin"
        >
          <geocomplete
            id="origin"
            name="origin"
            placeholder="Lieu de départ"
            title="Depuis"
            aria-label="Départ"
            :url="geoSearchUrl"
            :selected="origin"
            @geoSelected="selectedGeo"
          />
        </label>
      </div>
      <div class="column is-one-tenth has-text-centered">
        <img
          class="interchanged"
          src="images/PictoInterchanger.svg"
          alt="changer"
          @click="swap()"
        >
      </div>
      <div class="column has-text-centered is-one-third">
        <label
          class="label"
          for="destination"
        >
          <geocomplete
            id="destination"
            name="destination"
            placeholder="Lieu d'arrivée"
            title="Vers"
            :url="geoSearchUrl"
            :selected="destination"
            @geoSelected="selectedGeo"
          />
        </label>
      </div>
      <div class="column has-text-centered">
        <label
          for="rechercher"
          class="label"
        >
          <a
            id="rechercher"
            class="button"
            :href="checkUrlValid ? urlToCall : null"
            alt="Rechercher un covoiturage"
            title="Rechercher"
          ><span>Rechercher</span>
          </a>
        </label>
      </div>
    </div>
  </div>
</template>

<script>

import moment from "moment";
import Geocomplete from "./Geocomplete";
// import BDatepicker from "buefy/src/components/datepicker/Datepicker";
export default {
  name: 'Homesearchform',
  components: {
    // BDatepicker,
    Geocomplete
  },
  props: {
    geoSearchUrl: {
      type: String,
      default: ""
    },
    route: {
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
      origin: {},
      destination: {},
      outwardDate: new Date(), 
      outwardTime: new Date(),
      baseUrl: window.location.origin,
      message: null
    };
  },
  computed: {
    // check if the minimal infos are available to have a valid url to launch the search 
    checkUrlValid(){
      return this.origin.addressLocality && this.destination.addressLocality && this.origin.latitude && this.origin.longitude && this.destination.latitude && this.destination.longitude && this.outwardDate && this.outwardTime 
    },
    // formate the date
    dateFormated() {
      return this.outwardDate ? moment(this.outwardDate).format('YYYYMMDD') : null ;
    },
    // format the time
    timeFormated() {
      return this.outwardTime ? moment(this.outwardTime).format('HHmmss') : null;
    },
    // formate the addresses and return nothing if not defined
    originStreetAddressFormated() {
      let originStreetAddress = this.origin.streetAddress.trim().toLowerCase().replace(/ /g, '+')
      return originStreetAddress !="" ? `${originStreetAddress}+` : "";
    },
    destinationStreetAddressFormated() {
      let destinationStreetAddress = this.destination.streetAddress.trim().toLowerCase().replace(/ /g, '+')
      return destinationStreetAddress !="" ? `${destinationStreetAddress}+` : "";
    },
    // formate the postalCodes and return nothing if not defined
    originPostalCodeFormated() {
      return this.originPostalCode ? `${this.origin.postalCode}+` : "";
    },
    destinationPostalCodeFormated() {
      return this.destinationPostalCode ? `${this.destination.postalCode}+` : "";
    },
    // creation of the url to call
    urlToCall() {
      return `${this.baseUrl}/${this.route}/${this.originStreetAddressFormated}${this.originPostalCodeFormated}${this.origin.addressLocality}/${this.destinationStreetAddressFormated}${this.destinationPostalCodeFormated}${this.destination.addressLocality}/${this.origin.latitude}/${this.origin.longitude}/${this.destination.latitude}/${this.destination.longitude}/${this.dateFormated}${this.timeFormated}/resultats`;  
    } 
  },

  methods: {
    selectedGeo(val) {
      let name = val.name;
      this[name] = val;
      
    },
    swap() {

      let tempOrigin = { ...this.origin }
      this.origin = { ...this.destination }
      this.destination = {...tempOrigin}
      this.origin.name = "origin"
      this.destination.name = "destination"
    }
  }
};
</script>
