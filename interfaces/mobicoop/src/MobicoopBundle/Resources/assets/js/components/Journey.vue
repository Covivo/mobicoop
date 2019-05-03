<template>
  <div>
    <section
      class="section"
      name="list" 
      tag="div"
    >
      <div class="tile is-ancestor">
        <div class="tile is-vertical is-12">
          <div
            v-for="(journey,index) in externalsJourneys"
            :key="index"
            :alt="journey.origin" 
            class="tile is-child results "
          >
            <div class="columns is-vcentered ">
              <div class="column">
                <p>{{ journey.driver.alias }}</p>
              </div>
              <div class="column">
                <p>🚙</p><p />
              </div>
              <div class="column">
                <p>{{ journey.driver.alias }}</p><p />
              </div>
              <div class="column">
                <p>note</p><p />
              </div>
              <div class="column">
                <a href="">contacter</a>
              </div>		
            </div>
            <div class="columns is-vcentered ">
              <div class="column">
                <p>{{ journey.outward.mindate }}</p><p />
              </div>
              <div class="column">
                <p>{{ journey.outward.mindate }}</p>
                <p>{{ journey.from.city }}</p>
              </div>
              <div class="column">
                <p>{{ journey.outward.mindate }}</p>
                <p>{{ journey.to.city }}</p>
              </div>
              <div class="column">
                <p>tarif</p>
              </div>
            </div>
            <div class="columns is-vcentered ">
              <div class="column">
                <p>bouton voir detail</p><p />
              </div>
              <div class="column">
                <p>source</p>
              </div>
            </div>	
          </div>
        </div>
      </div>
    </section>
    <div class="column is-full is-centered">
      <span 
        v-if="externalsJourneys.length == 0" 
        class="tag is-warning"
      >Pas de voyage trouvé.</span>
    </div>
  </div>
</template>

<script>
// Requirement libs
import axios from 'axios';

// This function create promises to contact API's later

export default {
  props: {
    originLatitude: {
      type: String,
      default: ""
    },
    originLongitude: {
      type: String,
      default: ""
    },
    destinationLatitude: {
      type: String,
      default: ""
    },
    destinationLongitude: {
      type: String,
      default: ""
    },
  },
  data () {
    return {
      externalsJourneys: []
    }
  },
  computed: {
    
  },
  mounted () {
    axios
      .get(this.constructProviderUrl())
      .then(res => {
        let providersList = (res.data);
        for (let provider of providersList) {
          axios
            .get(this.constructJourneyURL(provider.name))
            .then(res => {
              let journeysList = (res.data);
              let journeysListFixed = journeysList.map(journey=> journey.journeys);
              this.externalsJourneys.push(...journeysListFixed);
            }) 
            .catch(err=> {
              console.error(err)
            })
        };
      })
  },
  methods: {
    constructProviderUrl() {
      return window.location.origin+'/provider/rdex?'
    },  
    constructJourneyURL(providerName) {
      return window.location.origin+`/journey/rdex?provider=${providerName}&driver=1&passenger=1&from_latitude=${this.destinationLatitude}&from_longitude=${this.destinationLongitude}&to_latitude=${this.originLatitude}&to_longitude=${this.originLongitude}`
    },
  }
};
</script>
