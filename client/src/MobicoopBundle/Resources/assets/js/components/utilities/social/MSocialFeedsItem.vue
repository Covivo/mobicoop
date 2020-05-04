<template>
  <div
    class="embedIframe"
    style="overflow:scroll; height:700px;width:100%;"
    @click="openRequestedPopup()"
  >
    <div  
      style="pointer-events:none;"
      v-html="iFrameString"
    />
  </div>
</template>
<script>
export default {
  props:{
    iFrameString:{
      type: String,
      default: null
    }
  },
  data() {
    return {
      source: null
    }
  },
  mounted() {
    let urlCurrent = JSON.parse(JSON.stringify(this.iFrameString.replace(/"/g,"'")));
    let regex = /<iframe.*?src='(.*?)'/;
    this.source = regex.exec(urlCurrent)[1];
  },
  methods:{
    openRequestedPopup(){
      let windowObjectReference;
      let source = this.source;
      windowObjectReference = window.open(
        source ,
        "DescriptiveWindowName",
        "resizable,scrollbars,status"
      );
    }
  }
}
</script>
