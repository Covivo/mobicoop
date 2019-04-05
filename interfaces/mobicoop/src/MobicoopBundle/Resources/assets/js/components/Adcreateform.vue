<template>
  <section class="section">
    <div class="tile is-ancestor">
      <div class="tile is-vertical is-12">
        <div class="tile is-child center-all">
          <form-wizard
            @on-complete="onComplete"
            back-button-text="Précèdent"
            next-button-text="Suivant"
            finish-button-text="Je partage mon annonce"
            title="Déposer une annonce"
            subtitle="Suivez les étapes.."
            color="#023D7F"
            class="tile is-vertical is-8"
          >
            <!-- ROLE -->
            <tab-content title="Vous êtes" icon="fa fa-user-friends" class="tabContent">
              <h3>Je suis:</h3>
              <b-field class="fieldsContainer">
                <b-radio-button
                  v-model="form.role"
                  name="role"
                  :native-value="1"
                  type="is-mobicoopblue"
                >
                  <b-icon icon="close"></b-icon>
                  <span>🚙 Conducteur</span>
                </b-radio-button>
                <b-radio-button
                  v-model="form.role"
                  name="role"
                  :native-value="2"
                  type="is-mobicooppink"
                >
                  <b-icon icon="check"></b-icon>
                  <span>👨‍⚖️ Passager</span>
                </b-radio-button>
                <b-radio-button
                  v-model="form.role"
                  name="role"
                  :native-value="3"
                  type="is-mobicoopgreen"
                >Passager ou Conducteur</b-radio-button>
              </b-field>
            </tab-content>
            <!-- TYPE TRAJET -->
            <tab-content title="Trajet" icon="fa fa-route" class="tabContent">
              <h3>Détails de votre trajet</h3>
              <b-field class="fieldsContainer">
                <b-radio-button
                  v-model="form.type"
                  name="type"
                  :native-value="1"
                  type="is-mobicoopblue"
                >
                  <b-icon pack="fas" icon="long-arrow-alt-right"></b-icon>
                  <span>Allez</span>
                </b-radio-button>
                <b-radio-button
                  v-model="form.type"
                  name="type"
                  :native-value="2"
                  type="is-mobicoopblue"
                >
                  <b-icon pack="fas" icon="exchange-alt"></b-icon>
                  <span>Allez/Retour</span>
                </b-radio-button>
              </b-field>
              <geocomplete
                name="origin"
                placeholder="Depuis"
                :url="geoSearchUrl"
                @geoSelected="selectedGeo"
              ></geocomplete>
              <b-icon
                pack="fas"
                type="is-mobicoopblue"
                :icon="form.type === 2 ? 'arrows-alt-v' : 'long-arrow-alt-down'"
                size="is-large"
              ></b-icon>
              <geocomplete
                placeholder="Vers"
                :url="geoSearchUrl"
                name="destination"
                @geoSelected="selectedGeo"
              ></geocomplete>
            </tab-content>
            <!-- FREQUENCY & Submit -->
            <tab-content title="Fréquence" icon="fa fa-calendar-check" class="tabContent">
              <h3>Fréquence du trajet:</h3>
              <b-field class="fieldsContainer">
                <b-radio-button
                  v-model="form.frequency"
                  name="frequency"
                  :native-value="1"
                  type="is-mobicoopblue"
                >
                  <b-icon icon="close"></b-icon>
                  <span>Ponctuel</span>
                </b-radio-button>
                <b-radio-button
                  v-model="form.frequency"
                  name="frequency"
                  :native-value="2"
                  type="is-mobicoopblue"
                >
                  <b-icon icon="check"></b-icon>
                  <span>Regulier</span>
                </b-radio-button>
              </b-field>
              <!-- DATE, TIME , MARGIN -->
              <div class="columns" >
                <div class="column">
                  <h5 class="title column is-full">Aller</h5>
                  <b-datepicker
                    :placeholder="form.frequency ===2 ? 'Date de début' : 'Date de départ...'"
                    v-model="form.outwardDate"
                    :day-names="daysShort"
                    :month-names="months"
                    :first-day-of-week="1"
                    class="column is-full"
                    position="is-top-right"
                    icon-pack="fas"
                  ></b-datepicker>
                  <div class="column is-full">
                    <div class="columns" v-for="(day,index) in nbOfDaysToPlan" :key="index">
                      <div v-if="nbOfDaysToPlan>1" class="column is-2 dayNameColumn">
                        <a class="button is-mobicoopblue is-2">{{days[index]}}</a>
                      </div>
                      <b-timepicker
                        class="column"
                        v-model="form.outwardTime"
                        placeholder="Heure de départ..."
                      >
                        <button
                          class="button is-mobicoopgreen"
                          @click="form.outwardTime = new Date()"
                        >
                          <b-icon icon="clock"></b-icon>
                          <span>Maintenant</span>
                        </button>
                        <button class="button is-mobicooppink" @click="form.outwardTime = null">
                          <b-icon icon="close"></b-icon>
                          <span>Effacer</span>
                        </button>
                      </b-timepicker>
                      <!-- MARGIN -->
                      <b-select
                        class="column is-4"
                        placeholder="Marge"
                        v-model="form.outwardMargin"
                      >
                        <option
                          v-for="(margin,key) in marginsMn"
                          :value="margin"
                          :key="key"
                        >{{ (1 > margin/60 > 0) ? margin : `${Math.trunc(margin/60)}H${margin%60}` }}</option>
                      </b-select>
                    </div>
                  </div>
                </div>
                <!-- RETURN -->
                <div class="column" v-if="form.type === 2">
                  <h2 class="title column is-full">Retour</h2>
                  <b-datepicker
                    :placeholder="form.frequency ===2 ? 'Date de fin' : 'Date de retour...'"
                    icon="calendar-today"
                    class="column is-full"
                    v-model="form.returnDate"
                  ></b-datepicker>
                  <div class="columns" v-for="(day,index) in nbOfDaysToPlan" :key="index">
                    <div v-if="nbOfDaysToPlan>1" class="column is-2 dayNameColumn">
                      <a class="button is-mobicoopblue is-2">{{days[index]}}</a>
                    </div>
                    <b-timepicker
                      v-model="form.returnTime"
                      placeholder="heure de retour..."
                      class="column"
                    >
                      <button class="button is-mobicoopgreen" @click="form.returnTime = new Date()">
                        <b-icon icon="clock"></b-icon>
                        <span>Maintenant</span>
                      </button>
                      <button class="button is-mobicooppink" @click="form.returnTime = null">
                        <b-icon icon="close"></b-icon>
                        <span>Effacer</span>
                      </button>
                    </b-timepicker>
                    <!-- MARGIN -->
                    <b-select
                      class="column is-4"
                      placeholder="Marge"
                      v-model="form.returnMargin"
                    >
                      <option
                        v-for="(margin,key) in marginsMn"
                        :value="margin"
                        :key="key"
                      >{{ (1 > margin/60 > 0) ? margin : `${Math.trunc(margin/60)}H${margin%60}` }}</option>
                    </b-select>
                  </div>
                </div>
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
import moment from 'moment'
import Geocomplete from "./Geocomplete";
export default {
  components: {
    Geocomplete
  },
  props: {
    sentFrequency: {
      type: Number,
      default: 1
    },
    sentRole: {
      type: Number,
      default: 1
    },
    sentType: {
      type: Number,
      default: 1
    },
    geoSearchUrl: {
      type: String,
      default: ""
    },
    sentOutward: {
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
      origin: null,
      outward: this.sentOutward,
      timeStart: new Date(),
      timeReturn: new Date(),
      days: [
        "dimanche",
        "lundi",
        "mardi",
        "mercredi",
        "jeudi",
        "vendredi",
        "samedi"
      ],
      daysEn: [
        "Sunday",
        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday",
        "Saturday"
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
      // margins in minutes
      marginsMn: [5, 10, 15, 20, 25, 30, 45, 60, 90, 120, 150],
      form: {
        createToken: this.sentToken,
        origin: "",
        originStreetAddress : null,
        originPostalCode: null,
        originAddressLocality: null,
        originAddressCountry: null,
        originLatitude: null,
        originLongitude: null,
        destinationLatitude: null,
        destinationLongitude: null,
        destination: "",
        destinationStreetAddress: null,
        destinationPostalCode: null,
        destinationAddressLocality: null,
        destinationAddressCountry: null,
        role: this.sentRole,
        type: this.sentType,
        frequency: this.sentFrequency,
        outwardDate: null,
        outwardMargin: null,
        outwardTime: null,
        returnDate: null,
        returnMargin: null,
        returnTime: null,
        // //Monday
        // monday:{
        //   outwardDate: null,
        //   outwardMargin: null,
        //   outwardTime: null,
        // }
        outwardMonTime: null,
        outwardMonMargin: null,
        returnMonTime: null,
        returnMonMargin: null,
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
     * Send the form to the route /covoiturage/annonce/poster
     */
    onComplete() { 
      let adForm = new FormData();
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
        adForm.append(renamedProp, value);
      }
      //  We post the form 🚀
      axios
        .post("/covoiturage/annonce/poster", adForm, {
          headers: {
            "Content-Type": "multipart/form-data"
          }
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
