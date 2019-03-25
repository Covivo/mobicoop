<template>
  <section class="section">
    <div class="tile is-ancestor">
      <div class="tile is-vertical is-12">
        <div class="tile is-child notification center-all">
          <form-wizard
            @on-complete="onComplete"
            back-button-text="Précèdent"
            next-button-text="Suivant"
            finish-button-text="Je partage mon annonce"
            title="Déposer une annonce"
            subtitle="Suivez les étapes.."
            color="#023D7F"
            class="tile is-vertical is-6"
          >
            <!-- ROLE -->
            <tab-content title="Vous êtes" icon="fa fa-user-friends" class="tabContent">
              <h3>Je suis:</h3>
              <b-field class="fieldsContainer">
                <b-radio-button v-model="role" name="role" :native-value="1" type="is-mobicoopblue">
                  <b-icon icon="close"></b-icon>
                  <span>🚙 Conducteur</span>
                </b-radio-button>
                <b-radio-button v-model="role" name="role" :native-value="2" type="is-mobicooppink">
                  <b-icon icon="check"></b-icon>
                  <span>👨‍⚖️ Passager</span>
                </b-radio-button>
                <b-radio-button
                  v-model="role"
                  name="role"
                  :native-value="3"
                  type="is-mobicoopgreen"
                >Passager ou Conducteur</b-radio-button>
              </b-field>
            </tab-content>
            <!-- Trajet -->
            <tab-content title="Trajet" icon="fa fa-route" class="tabContent">
              <h3>Détails de votre trajet</h3>
              <b-field class="fieldsContainer">
                <b-radio-button v-model="type" name="type" :native-value="1" type="is-mobicoopblue">
                  <b-icon pack="fas" icon="long-arrow-alt-right"></b-icon>
                  <span>Allez</span>
                </b-radio-button>
                <b-radio-button v-model="type" name="type" :native-value="2" type="is-mobicoopblue">
                  <b-icon pack="fas" icon="exchange-alt"></b-icon>
                  <span>Allez/Retour</span>
                </b-radio-button>
              </b-field>
              <geocomplete placeholder="Depuis" :url="geoSearchUrl"></geocomplete>
              <b-icon
                pack="fas"
                type="is-mobicoopblue"
                :icon="type === 2 ? 'arrows-alt-v' : 'long-arrow-alt-down'"
                size="is-large"
              ></b-icon>
              <geocomplete placeholder="Vers" :url="geoSearchUrl"></geocomplete>
            </tab-content>
            <!-- FREQUENCY & Submit -->
            <tab-content title="Fréquence" icon="fa fa-calendar-check" class="tabContent">
              <h3>Fréquence du trajet:</h3>
              <b-field class="fieldsContainer">
                <b-radio-button
                  v-model="frequency"
                  name="frequency"
                  :native-value="1"
                  type="is-mobicoopblue"
                >
                  <b-icon icon="close"></b-icon>
                  <span>Ponctuel</span>
                </b-radio-button>
                <b-radio-button
                  v-model="frequency"
                  name="frequency"
                  :native-value="2"
                  type="is-mobicoopblue"
                >
                  <b-icon icon="check"></b-icon>
                  <span>Regulier</span>
                </b-radio-button>
              </b-field>
              <div v-if="frequency === 2">
                <div class="columns" v-for="(day,index) in days" :key="index">
                  <div class="column">
                    <h5 class="title">Aller ({{day}})</h5>
                    <b-datepicker placeholder="Date de départ..." icon="calendar-today"></b-datepicker>
                    <b-timepicker v-model="timeStart" placeholder="Heure de départ...">
                      <button class="button is-primary" @click="time = new Date()">
                        <b-icon icon="clock"></b-icon>
                        <span>Maintenant</span>
                      </button>
                      <button class="button is-danger" @click="time = null">
                        <b-icon icon="close"></b-icon>
                        <span>Effacer</span>
                      </button>
                    </b-timepicker>
                  </div>
                  <div class="column" v-if="type === 2">
                    <h5 class="title">Retour ({{day}})</h5>
                    <b-datepicker placeholder="Date de retour..." icon="calendar-today"></b-datepicker>
                    <b-timepicker v-model="timeReturn" placeholder="heure de retour...">
                      <button class="button is-primary" @click="time = new Date()">
                        <b-icon icon="clock"></b-icon>
                        <span>Maintenant</span>
                      </button>
                      <button class="button is-danger" @click="time = null">
                        <b-icon icon="close"></b-icon>
                        <span>Effacer</span>
                      </button>
                    </b-timepicker>
                  </div>
                </div>
              </div>
              <div class="columns" v-else>
                <div class="column">
                  <h5 class="title">Aller</h5>
                  <b-datepicker placeholder="Date de départ..." icon="calendar-today"></b-datepicker>
                  <b-timepicker v-model="timeStart" placeholder="Heure de départ...">
                    <button class="button is-primary" @click="time = new Date()">
                      <b-icon icon="clock"></b-icon>
                      <span>Maintenant</span>
                    </button>
                    <button class="button is-danger" @click="time = null">
                      <b-icon icon="close"></b-icon>
                      <span>Effacer</span>
                    </button>
                  </b-timepicker>
                </div>
                <div class="column" v-if="type === 2">
                  <h2 class="title">Retour</h2>
                  <b-datepicker placeholder="Date de retour..." icon="calendar-today"></b-datepicker>
                  <b-timepicker v-model="timeReturn" placeholder="heure de retour...">
                    <button class="button is-primary" @click="time = new Date()">
                      <b-icon icon="clock"></b-icon>
                      <span>Maintenant</span>
                    </button>
                    <button class="button is-danger" @click="time = null">
                      <b-icon icon="close"></b-icon>
                      <span>Effacer</span>
                    </button>
                  </b-timepicker>
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
    }
  },
  data() {
    return {
      frequency: this.sentFrequency,
      role: this.sentRole,
      type: this.sentType,
      outward: this.sentOutward,
      timeStart: new Date(),
      timeReturn: new Date(),
      days: [
        "lundi",
        "mardi",
        "mercredi",
        "jeudi",
        "vendredi",
        "samedi",
        "dimanche"
      ],
      form: {
        origin: "",
        destination: "",
        role: "",
        type: false,
        frequency: null,
        fromDate: "",
        toDate: ""
      }
    };
  },
  mounted() {
    console.log("sentRole", this.sentRole);
  },
  methods: {
    sendForm() {
      console.log("Will send form");
    },
    onComplete() {
      let adForm = new FormData();
      for (let prop in this.form) {
        console.log(prop, this.form[prop]);
        adForm.append(prop, this.form[prop]);
      }
      console.log(adForm);
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
</style>
