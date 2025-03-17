export function setCookie(name, value, daysToLive) {
    const date = new Date();
    date.setTime(date.getTime() + daysToLive * 24 * 60 * 60 * 1000);
    let expires = "expires=" + date.toUTCString();
    document.cookie = `${name}=${value}; ${expires}; path=/`;
}

export function getCookie(name) {
    const cDecoded = decodeURIComponent(document.cookie);
    const cArray = cDecoded.split("; ");
    let result = null;

    cArray.forEach(function(element) {
        if (element.indexOf(name) == 0) {
            result = element.substring(name.length + 1);
        }
    });

    return result;
}

export async function verifyToken() {
    const token = getCookie("Token");

    if (token == null) {
        return false;
    }

    const response = await fetch("http://localhost:3000/tokens", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },

        body: JSON.stringify({ Token: token })
    });

    const json = await response.json();

    if (response.status == 400) {
        return false;
    } else if (response.status == 404) {
        return false;
    } else if (response.status == 410) {
        return false;
    } else if (response.status == 200) {
        return true;
    }
}

export async function getUserInfo() {
    const userFetch = await fetch("http://localhost:3000/tokens", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ GetInfo: "true", Token: getCookie("Token") })
    });

    if (userFetch.ok) {
        const json = await userFetch.json();
        return json;
    } else {
        return null;
    }
}