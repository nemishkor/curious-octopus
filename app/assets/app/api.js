const Api = {
    get: get,
    put: put,
    post: post,
    delete: _delete
}
export default Api;

function get(vueObject, url, successCallback) {
    fetch(
        `${process.env.API_URL}${url}`,
        {method: 'GET', headers: {'X-API-TOKEN': vueObject.$store.getters.token}}
    ).then(
        (response) => {
            vueObject.loading = false
            if (![200, 403].includes(response.status)) {
                vueObject.error = true
                console.error('Invalid response status from API: ' + response.status)
                return
            }
            response.json().then(
                (data) => {
                    if (response.status === 403) {
                        handleAccessDenied(vueObject, data)
                        return
                    }
                    successCallback(data)
                },
                (error) => {
                    vueObject.error = true;
                    console.error('Invalid response data from API: ' + error)
                }
            )
        },
        (any) => {
            vueObject.loading = false
            vueObject.error = true
            console.error('Error while creating order', any)
        }
    )
}

function post(vueObject, url, body, successCallback) {
    return postPut('POST', vueObject, url, body, successCallback)
}

function put(vueObject, url, body, successCallback) {
    return postPut('PUT', vueObject, url, body, successCallback)
}

function postPut(method, vueObject, url, body, successCallback) {
    return fetch(
        `${process.env.API_URL}${url}`,
        {
            method: method,
            headers: {
                'X-API-TOKEN': vueObject.$store.getters.token,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(body),
        }
    ).then(
        (response) => {
            vueObject.loading = false
            if (![200, 400, 403].includes(response.status)) {
                vueObject.error = true
                console.error('Invalid response status from API: ' + response.status)
                return
            }
            response.json().then(
                (data) => {
                    if (response.status === 403) {
                        handleAccessDenied(vueObject, data)
                        return
                    }
                    if (response.status === 400) {
                        if (data.hasOwnProperty('violationList')) {
                            vueObject.violations = data.violationList
                            return
                        }
                        vueObject.error = true
                        return
                    }
                    successCallback(data)
                },
                (error) => {
                    vueObject.error = true
                    console.error('Invalid response data from API: ' + error)
                }
            )
        },
        (any) => {
            vueObject.loading = false
            vueObject.error = true
            console.error('Error while creating order', any)
        }
    )
}

function _delete(vueObject, url, successCallback) {
    return fetch(
        `${process.env.API_URL}${url}`,
        {method: 'DELETE', headers: {'X-API-TOKEN': vueObject.$store.getters.token}}
    ).then(
        (response) => {
            vueObject.loading = false
            if (response.status === 403) {
                response.json().then(
                    (data) => {
                        handleAccessDenied(vueObject, data)
                    },
                    (error) => {
                        vueObject.error = true
                        console.error('Invalid response data from API: ' + error)
                    }
                )
                return
            }
            if (response.status !== 201) {
                vueObject.error = true
                console.error('Invalid response status from API: ' + response.status)
                return
            }
            successCallback()
        },
        (any) => {
            vueObject.loading = false
            vueObject.error = true
            console.error('Error while creating order', any)
        }
    );
}

function handleAccessDenied(vueObject, data) {
    if (!data.hasOwnProperty('message')) {
        vueObject.error = true
        console.error('Body does not have any messages. Unexpected "Access denied" response')
        return
    }
    if (data.message === 'Invalid credentials.') {
        vueObject.$store.commit('deleteUser')
        vueObject.$router.replace({name: 'login'})
        return
    }
    vueObject.errorMessage = data.message
}
