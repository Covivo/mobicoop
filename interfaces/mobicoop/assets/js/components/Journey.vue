<template>
  <div class="container externalJourney">
    <div class="spinerDiv">
      <fulfilling-bouncing-circle-spinner 
        v-if="spinner"
        :animation-duration="1500"
        :size="64"
        color="hsl(171, 100%, 41%)"
      />
    </div>
    <div 
      v-if="allApiResults.length" 
      class="columns is-multiline"
    >
      <div class="notification is-warning">
        <button class="delete" />
        <strong> No route found for this api</strong>
      </div>

      <div 
        v-for="(apiResult,index) in allApiResults" 
        :key="index" 
        class="column is-one-third"
      >
        <div class="box">
          <article class="media">
            <div class="media-left">
              <figure class="image is-64x64">
                <img 
                  class="is-rounded" 
                  src="https://bulma.io/images/placeholders/128x128.png" 
                  alt="Image"
                >
              </figure>
            </div>
            <div class="media-content">
              <div class="content">
                <strong>DRIVER {{ apiResult.journeys.driver.alias }}</strong> <small>{{ apiResult.journeys.driver.gender }}</small>
                <span class="tag">mindate  - maxdate</span>
                <span class="tag">{{ apiResult.journeys.uuid }}</span>
                <div class="columns">
                  <div class="column is-2">
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
                      {{ apiResult.journeys.from.city }}
                      <br>
                      {{ apiResult.journeys.to.city }}
                      <br>
                      {{ apiResult.journeys.details }}
                      <br>
                      <span 
                        class="tag" 
                        :alt="apiResult.journeys.origin"
                      >
                        <a :href="apiResult.journeys.origin">{{ apiResult.journeys.origin }}</a>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </article>
        </div>
      </div>
      <div class="notification is-warning">
        <button class="delete" />
        <strong>API STATE NOT FULFILLED</strong>
      </div>
    </div>
    <span class="tag is-warning">No external journey found.</span>
  </div>
</template>
<script>

// Requirement libs
import axios from 'axios';
import {FulfillingBouncingCircleSpinner} from 'epic-spinners';

// This function create promises to contact API's later
function getJourneyExternalApis(url,providerName) {
  return axios.get(`${url}external_journeys.json?provider_name=${providerName}&driver=1&passenger=1&from_latitude=48.69278&from_longitude=6.18361&to_latitude=49.11972&to_longitude=6.17694`);
}

// This is the main component Journey
export default {
  name: "Journey",
  components: {
    FulfillingBouncingCircleSpinner
  },
  props: {
    apiUri: {
      type: String,
      default: "L'api n'est pas renseignée"
    }
  },
  data () {
    return {
      listExternalApi: null,
      allApiResults : [],
      spinner: false
    }
  },
  mounted () {
    // Get list of provider
    axios
      .get(`${this.apiUri}external_journey_providers.json`)
      .then((response) =>{
        // Got list of external api name available, put it into memory
        this.listExternalApi = response.data;
        return response.data;
      }).then((listExternalApi)=>{
        for(let providerName of listExternalApi){
          getJourneyExternalApis(this.apiUri, providerName)
            .then((res)=>{
              // Data are coming & inserting, we enable spinner & add data to result array
              this.spinner = true;
              this.allApiResults.push(...res.data);
              setTimeout(()=>{
                // remove spinner after 1.5s dude ☢️
                this.spinner = false;
              },1500)
            })
        }
      })
  }
}
</script>