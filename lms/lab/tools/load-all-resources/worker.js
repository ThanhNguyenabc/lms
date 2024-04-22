onmessage = async function (event) {
  if (!event.data) return;

  const numOfRequest = 10;

  try {
    let { folder, baseImageUrl, baseUrl } = event.data;
    const res = await this.fetch(`${baseUrl}/api.php?folder=${folder}`).then(
      (res) => res.json()
    );

    const { data } = res;
    const pages = Math.ceil(data.length / numOfRequest);

    this.postMessage({
      completed: 0,
      status: "pending",
      dataLength: data.length,
    });

    let completed = 0;
    let currentPage = 1;

    while (currentPage <= pages) {
      const result = await loadData(
        currentPage,
        data,
        baseImageUrl,
        numOfRequest
      );
      completed += result;

      // send message to main thread
      this.postMessage({
        completed,
        status: currentPage == pages ? "done" : "processing",
        dataLength: data.length,
      });

      await new Promise((resolve) => this.setTimeout(() => resolve(), 2500));
      // increase page
      currentPage++;
    }
    completed = null;
    currentPage = null;
  } catch (error) {
    this.postMessage({
      status: "error",
    });
    console.log(`Error from:::: worker ${error}`);
  }
};

async function loadData(page, data, baseUrl, itemPerPages) {
  const start = (page - 1) * itemPerPages;
  let end = itemPerPages * page;

  if (end >= data.length) {
    end = data.length;
  }

  let promises = [];
  for (let i = start; i < end; i++) {
    promises.push(this.fetch(`${baseUrl}/${data[i]}`, { cache: "no-store" }));
  }
  await Promise.all(promises);
  promises = null;
  return end - start;
}
