<template>
  <div>
    <transition-group 
      name="list" 
      tag="div" 
      class="tile is-multiline section row columns"
    >
      <div
        v-for="(journey,index) in externalsJourneys"
        :key="index"
        class="column is-half"
        :alt="journey.origin"
      >
        <div class="box">
          <article class="media">
            <div class="media-left">
              <i 
                :class="journey.frequency === 'regular' ? 'far fa-calendar-alt' : 'far fa-calendar'" 
                :alt="journeyfrequency"
              />
            </div>
            <div class="media-content">
              <div class="content">
                <strong>🚙 {{ journey.driver.alias }}</strong> <small>{{ journey.driver.gender == 'male' ? '♂' : '♀' }}</small>
                <span class="tag">{{ journey.outward.mindate }}  - {{ journey.outward.maxdate }} </span>
                <span class="tag">{{ journey.uuid }}</span>
                <div class="columns">
                  <div class="column is-3">
                    <div style="line-height: 1.8;">
                      <span class="tag is-primary is-rounded">From</span>
                      <br>
                      <span class="tag is-primary is-rounded">To</span>
                      <br>
                      <span class="tag is-info is-rounded">Info</span>
                      <br>
                      <span 
                        class="icon has-text-info" 
                        style="margin-left: 7px"
                      >
                        <i class="fas fa-info-circle" />
                      </span>
                    </div>
                  </div>
                  <div class="column">
                    <div style="line-height: 1.8">
                      {{ journey.from.city }}
                      <br>
                      {{ journey.to.city }}
                      <br>
                      {{ journey.details }}
                      <br>
                      <span 
                        class="tag" 
                        :alt="journey.origin"
                      >
                        <a :href="journey.url">{{ journey.origin }} </a>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </article>
        </div>
      </div>
    </transition-group>
    <span 
      v-if="!externalsJourneys" 
      class="tag is-warning"
    >No external journey found.</span>
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
    } 
  }
};
</script>
