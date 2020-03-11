import axios from 'axios';

export const getJwt = function() {
  if (document.getElementById('extkey')) {
    return 'Bearer ' + atob(document.getElementById('extkey').value);
  }

};
const wrapper = promise => (
    promise.then(data => ({data: data.data, error: null})).
        catch(error => ({error: error.response.data, data: null}))
);

export async function callApi(uri, method, data = null) {
  return await wrapper(axios.request({
    url: uri,
    method: method,
    data: data,
    headers: {
      'Authorization': getJwt(),
      'Accept': 'application/ld+json',
      'Content-type': 'application/ld+json',
    },
  }));
};

export async function callController(uri, method, data = null) {
  return axios.request({
    url: uri,
    method: method,
    data: data,
    headers: {
      'Accept': 'application/json',
      'Content-type': 'application/json',
    },
  });
}
