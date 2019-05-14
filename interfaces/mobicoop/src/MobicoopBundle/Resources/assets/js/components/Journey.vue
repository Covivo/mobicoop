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
            class="tile is-child resultsV1 "
          >
            <div class="columns is-vcentered first">
              <div class="column">
                <p>{{ journey.driver.alias }}</p>
              </div>
              <div class="column">
                <p class="is-pulled-right">
                  <i 
                    :class="journey.frequency === 'regular' ? 'far fa-calendar-alt' : 'far fa-calendar'"
                  />
                </p>
              </div>
            </div>
            <div class="columns is-vcentered second">
              <div class="column">
                <p>Du {{ journey.outward.mindate | formatDate }}</p>
                <p>au {{ journey.outward.maxdate | formatDate }}</p>
              </div>
              <div class="column">
                <p>{{ journey.from.city }}</p>
              </div>
              <div class="column">
                <p><i class="fas fa-long-arrow-alt-right" /></p>
              </div>
              <div class="column">
                <p>{{ journey.to.city }}</p>
              </div>
            </div>
            <div class="columns is-vcentered ">
              <div class="column is-pulled-right">
                <a
                  class="button source is-outlined is-pulled-right"
                  :href="journey.url.includes(journey.origin) ? `https://${journey.url}` : `https://${journey.origin}${journey.url}`"
                >{{ journey.origin }} </a>
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
              let journeysListFixed = journeysList.map(journey => journey.journeys);
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
      return window.location.origin+`/journey/rdex?provider=${providerName}&driver=1&passenger=1&from_latitude=${this.originLatitude}&from_longitude=${this.originLongitude}&to_latitude=${this.destinationLatitude}&to_longitude=${this.destinationLongitude}`
    },
  }
};
</script>
