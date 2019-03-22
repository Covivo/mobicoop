<template>
  <section class="section">
    <div class="tile is-ancestor">
      <div class="tile is-vertical is-12">
        <!--  Choose Role  -->
        <div class="tile is-child notification center-all">
          <h3>Je suis:</h3>
          <b-field>
            <b-radio-button v-model="role" name="role" native-value="1" type="is-mobicoop-blue">
              <b-icon icon="close"></b-icon>
              <span>🚙 Conducteur</span>
            </b-radio-button>
            <b-radio-button v-model="role" name="role" native-value="2" type="is-mobicoop-pink">
              <b-icon icon="check"></b-icon>
              <span>👨‍⚖️ Passager</span>
            </b-radio-button>
            <b-radio-button
              v-model="role"
              name="role"
              native-value="3"
              type="is-mobicoop-green"
            >Passager ou Conducteur</b-radio-button>
          </b-field>
        </div>
         <!-- Choose type -->
        <div class="tile is-parent is-12">
          <div class="tile is-child notification is-6 center-all">
            <h3>Type de trajet:</h3>
            <b-field>
              <b-radio-button v-model="type" name="type" native-value="1" type="is-mobicoop-blue">
                <b-icon icon="close"></b-icon>
                <span>Allez</span>
              </b-radio-button>
              <b-radio-button v-model="type" name="type" native-value="2" type="is-mobicoop-green">
                <b-icon icon="check"></b-icon>
                <span>Allez/Retour</span>
              </b-radio-button>
            </b-field>
          </div>
           <!-- Choose Frequency  -->
          <div class="tile is-child notification is-6 center-all">
            <h3>Fréquence du trajet:</h3>
            <b-field>
              <b-radio-button
                v-model="frequency"
                name="frequency"
                native-value="1"
                type="is-mobicoop-blue"
              >
                <b-icon icon="close"></b-icon>
                <span>Ponctuel</span>
              </b-radio-button>
              <b-radio-button
                v-model="frequency"
                name="frequency"
                native-value="2"
                type="is-mobicoop-green"
              >
                <b-icon icon="check"></b-icon>
                <span>Regulier</span>
              </b-radio-button>
            </b-field>
          </div>
        </div>
      </div>
    </div>

    <div v-if="frequency === '2'">
      <div class="columns" v-for="(day,index) in days" :key="index">
        <div class="column">
          <h5 class="title">Aller (${day})</h5>
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
        <div class="column" v-if="type === '2'">
          <h5 class="title">Retour (${day})</h5>
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
      <div class="column" v-if="type === '2'">
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
    <a @click="sendForm" class="button is-success">Je partage mon annonce</a>
    {# {{ form_start(form) }}
    {# {{ form_row(form._token) }} #}
    {# {{ form_row(form.submit) }}
    {{ form_end(form, {'render_rest': false}) }} #}
    <p class="content">
      <b>Selection:</b>
      ${ role }
    </p>
  </section>
</template>

<script>
export default {
  name: 'AdCreateForm',
  props:{
    frequency: {
      type: String,
      default: '1'
    },
    role: {
      type: String,
      default: ''
    },
    type: {
      type: String,
      default: '1'
    },
    outward: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      timeStart: new Date(),
      timeReturn: new Date(),
      days: ['lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche'],
      form: {
        origin: '',
        destination: '',
        role: '',
        type: false,
        frequency: null,
        fromDate: '',
        toDate: ''
      }
    };
  },
  mounted(){
    console.log('freq', this.outward);
  },
  methods:{
    sendForm(){
      console.log('Will send form');
    }
  }
};
</script>

<style>
</style>
