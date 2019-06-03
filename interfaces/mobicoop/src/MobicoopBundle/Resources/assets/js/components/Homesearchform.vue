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
            value="tata"
            :url="geoSearchUrl"
            @geoSelected="selectedGeo"
          />
        </label>
      </div>
      <!-- Commented and not removed because it can be usefull later if we'll implement the possibility to choose a date and an hour for the simple search -->
      <!-- datepicker -->
      <!-- <label
                class="label"
                for="dateDepart"
              >Date de départ
                <b-datepicker
                  id="dateDepart"
                  v-model="outwardDate"
                  :placeholder="'Date de départ...'"
                  title="Date de départ"
                  :day-names="daysShort"
                  :month-names="months"
                  :first-day-of-week="1"
                  position="is-top-right"
                  icon-pack="fas"
                  editable
                />
              </label> -->
      <!-- timepicker -->
      <!-- <label
                class="label"
                for="heureDepart"
              >Heure de départ
                <b-timepicker
                  id="heureDepart"
                  v-model="outwardTime"
                  placeholder="Heure de départ..."
                  title="Heure de départ"
                >
                  <button
                    class="button is-mobicoopgreen"
                    @click="outwardTime = new Date()"
                  >
                    <b-icon icon="clock" />
                    <span>Maintenant</span>
                  </button>
                  <button
                    class="button is-mobicooppink"
                    @click="outwardTime = null"
                  >
                    <b-icon icon="close" />
                    <span>Effacer</span>
                  </button>
                </b-timepicker>
              </label> -->
      <!-- search button -->
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
      originLatitude: null,
      originLongitude: null,
      originStreetAddress : null,
      originPostalCode: null,
      originAddressLocality: null,
      destinationLatitude: null,
      destinationLongitude: null,
      destinationStreetAddress: null,
      destinationPostalCode: null,
      destinationAddressLocality: null,
      outwardDate: new Date(), 
      outwardTime: new Date(),
      baseUrl: window.location.origin,
    };
  },
  computed: {
    // check if the minimal infos are available to have a valid url to launch the search 
    checkUrlValid(){
      return this.originAddressLocality && this.destinationAddressLocality && this.originLatitude && this.originLongitude && this.destinationLatitude && this.destinationLongitude && this.outwardDate && this.outwardTime 
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
      let originStreetAddress = this.originStreetAddress.trim().toLowerCase().replace(/ /g, '+')
      return originStreetAddress !="" ? `${originStreetAddress}+` : "";
    },
    destinationStreetAddressFormated() {
      let destinationStreetAddress = this.destinationStreetAddress.trim().toLowerCase().replace(/ /g, '+')
      return destinationStreetAddress !="" ? `${destinationStreetAddress}+` : "";
    },
    // formate the postalCodes and return nothing if not defined
    originPostalCodeFormated() {
      return this.originPostalCode ? `${this.originPostalCode}+` : "";
    },
    destinationPostalCodeFormated() {
      return this.destinationPostalCode ? `${this.destinationPostalCode}+` : "";
    },
    // creation of the url to call
    urlToCall() {
      return `${this.baseUrl}/${this.route}/${this.originStreetAddressFormated}${this.originPostalCodeFormated}${this.originAddressLocality}/${this.destinationStreetAddressFormated}${this.destinationPostalCodeFormated}${this.destinationAddressLocality}/${this.originLatitude}/${this.originLongitude}/${this.destinationLatitude}/${this.destinationLongitude}/${this.dateFormated}${this.timeFormated}/resultats`;  
    } 
  },

  methods: {
    selectedGeo(val) {
      let name = val.name;
      this[name + "Latitude"] = val.latitude;
      this[name + "Longitude"] = val.longitude;
      this[name + "StreetAddress"] = val.streetAddress;
      this[name + "PostalCode"] = val.postalCode;
      this[name + "AddressCountry"] = val.addressCountry;
      this[name + "AddressLocality"] = val.addressLocality;
    },
    swap: function () {
      if (this.originAddressLocality != null && this.destinationAddressLocality != null) {
        let originLatitudeTemp = this.originLatitude
        let originLongitudeTemp = this.originLongitude
        let originStreetAddressTemp = this.originStreetAddress 
        let originPostalCodeTemp = this.originPostalCode
        let originAddressLocalityTemp = this.originAddressLocality
        
        this.originLatitude = this.destinationLatitude
        this.originLongitude = this.destinationLongitude
        this.originStreetAddress = this.destinationStreetAddress
        this.originPostalCode = this.destinationPostalCode
        this.originAddressLocality = this.destinationAddressLocality

        this.destinationLatitude = originLatitudeTemp
        this.destinationLongitude = originLongitudeTemp
        this.destinationStreetAddress = originStreetAddressTemp
        this.destinationPostalCode = originPostalCodeTemp
        this.destinationAddressLocality = originAddressLocalityTemp

         
      }
    }
  }
};
</script>

<style lang="scss" scoped>

.fieldsContainer {
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>