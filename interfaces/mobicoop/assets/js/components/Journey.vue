<template>
  <div>
    <div class="box cta">
      <div 
        class="columns is-mobile is-centered"
      >
        <div class="field is-grouped is-grouped-multiline">
          <div 
            v-for="(partner,indexApi) in listExternalApi"
            :key="indexApi" 
            class="control"
          ><span :class="`tag is-${indexApi} is-large`">{{ indexApi }} {{ partner.result }}</span>
          </div>
          <div class="spinerDiv">
            <!-- This is the spinner loader when data are comming -->
            <transition name="fade">
              <self-building-square-spinner 
                v-if="spinner"
                :animation-duration="1500"
                :size="40"
                color="hsl(204, 86%, 53%)"
              />
            </transition>
          </div>
        </div>
      </div>
    </div>
    <div class="externalJourney container">
      <div class="tile is-ancestor">
        <aside class="menu tile is-vertical is-3">
          <p class="menu-label">
            Proposals found:
          </p>
          <ul class="menu-list">
            <li>
              <span class="tag is-info is-large">{{ tweenedNbOfResults.toFixed(0) }}</span>
            </li>
          </ul>
          <p class="menu-label">
            Filters
          </p>
          <ul class="menu-list">
            <li>
              <a class="is-active">Plateform</a>
              <ul>
                <li 
                  v-for="(partner,indexApi) in listExternalApi"
                  :key="
                  indexApi"
                >
                  <div class="field">
                    <input
                      :id="`${indexApi}Checkbox`" 
                      v-model="selectedPlateform" 
                      class="is-checkradio" 
                      type="checkbox" 
                      :name="`${indexApi}Checkbox`"
                    >
                    <label :for="`${indexApi}Checkbox`">{{ indexApi }}</label>
                  </div>
                </li>
              </ul>
            </li>
          </ul>
          <p class="menu-label">
            Prix
          </p>
          <ul class="menu-list">
            <li>
              <input 
                class="slider is-fullwidth is-info" 
                step="1" 
                min="0" 
                max="100" 
                value="50" 
                type="range"
              >
            </li>
          </ul>
        </aside>
        <transition-group 
          name="list" 
          tag="div" 
          class="tile is-multiline section row columns"
        >

          <div 
            v-for="(apiResult,index) in allApiResults" 
            :key="index" 
            class="column is-half resultsItem"
            :alt="apiResult.journeys.origin"
          >
            <!-- TODO, separete box into another component to be reused -->
            <div class="box">
              <article class="media">
                <div class="media-left">
                  <figure class="image is-64x64">
                    <img 
                      class="is-rounded" 
                      :src="randomPicture()" 
                      alt="Image"
                    >
                  </figure>
                  <i 
                    :class="apiResult.journeys.frequency === 'regular' ? 'far fa-calendar-alt' : 'far fa-calendar'" 
                    :alt="apiResult.journeys.frequency"
                  />
                </div>
                <div class="media-content">
                  <div class="content">
                    <strong>🚙 {{ apiResult.journeys.driver.alias }}</strong> <small>{{ apiResult.journeys.driver.gender == 'male' ? '♂' : '♀' }}</small>
                    <span class="tag">{{ apiResult.journeys.outward.mindate }}  - {{ apiResult.journeys.outward.maxdate }} </span>
                    <span class="tag">{{ apiResult.journeys.uuid }}</span>
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
                            <a :href="apiResult.journeys.url">{{ apiResult.journeys.origin }}</a>
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
          v-if="!allApiResults" 
          class="tag is-warning"
        >No external journey found.</span>
      </div>
    </div>
  </div>
</template>

<script>
// Requirement libs
import axios from 'axios';
import {SelfBuildingSquareSpinner} from 'epic-spinners';
import {TweenLite} from "gsap/TweenMax";

// This function create promises to contact API's later
function getJourneyExternalApis(url,providerName,geoInfos) {
  return axios.get(`${url}external_journeys.json?provider_name=${providerName}&driver=1&passenger=1&from_latitude=${geoInfos.latStart}&from_longitude=${geoInfos.longStart}&to_latitude=${geoInfos.latEnd}&to_longitude=${geoInfos.longEnd}`);
}

// This is the main component Journey
export default {
  name: "Journey",
  components: {
    SelfBuildingSquareSpinner
  },
  // props are send by backend ! 👌
  props: {
    apiUri: {
      type: String,
      default: "L'api n'est pas renseignée"
    },
    searchUser:{
      type: String,
      default: ''
    },
    geoInfos: {
      type: Object,
      default: function () {
        return {}
      }
    }
  },
  data () {
    return {
      listExternalApi: {},
      allApiResults : [],
      selectedPlateform: [],
      spinner: false,
      nbOfResults: 0,
      tweenedNbOfResults: 0
    }
  },
  // We need to watch if there are changed into those variables
  watch: {
    nbOfResults: function(val){
      TweenLite.to(this.$data, 1, { tweenedNbOfResults: val});
    },
    // Geoinfo is edited by Searchgeocoding component
    geoInfos: {
      deep: true,// this option is important to watch all property of the obj
      handler(val){
        this.resetSearch(); // We need to reset everything between search!
        for(let providerName in this.listExternalApi){
          getJourneyExternalApis(this.apiUri, providerName, this.geoInfos)
            .then((res)=>{
              // Data are coming & inserting, we enable spinner & add data to result array
              this.spinner = true;
              this.allApiResults.push(...res.data);
              // updated stats about joruneys found
              this.listExternalApi[providerName].result += res.data.length;
              this.nbOfResults += res.data.length;
              setTimeout(()=>{
                // remove spinner after 1.5s dude ☢️
                this.spinner = false;
              },1500)
            })
        }
      }
    }
  },
  mounted () {
    // Get list of provider when Journey compnent is loaded
    axios
      .get(`${this.apiUri}external_journey_providers.json`)
      .then((response) =>{
        // Got list of external api name available, put it into binded variable obj
        for(let apiName of response.data){
          this.listExternalApi[apiName] = {
            result: 0
          };
        }
      })
  },
  methods:{
    resetSearch(){
      this.spinner = true;
      this.allApiResults = [];
      this.nbOfResults = 0;
      for(let apiName in this.listExternalApi){
        this.listExternalApi[apiName] = {
          result: 0
        };
      }
      this.spinner = false;
    },
    randomPicture(){
      return `https://picsum.photos/200/200/?random&r=${Math.floor(Math.random()*10) + 1}`
    } 
  },
}
</script>